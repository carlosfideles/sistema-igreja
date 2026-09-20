<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Member;
use App\Models\MemberTransfer;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    public function test_can_create_member_with_valid_data(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $church = $this->createChurch();

        $this->actingAs($admin);

        $response = $this->post('/members', [
            'church_id' => $church->id,
            'full_name' => 'Barnabé da Silva',
            'cpf' => '123.456.789-00',
            'phone' => '(61) 98888-7777',
            'gender' => 'M',
            'marital_status' => 'casado',
            'status' => 'ativo',
            'entry_date' => '2025-01-10',
        ]);

        $member = Member::where('full_name', 'Barnabé da Silva')->first();
        $this->assertNotNull($member);
        $response->assertRedirect("/members/{$member->id}");

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'church_id' => $church->id,
            'full_name' => 'Barnabé da Silva',
            'status' => 'ativo',
        ]);
    }

    public function test_can_update_member_information(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $church = $this->createChurch();
        $member = $this->createMember($church, ['full_name' => 'Nome Antigo']);

        $this->actingAs($admin);

        $response = $this->put("/members/{$member->id}", [
            'church_id' => $church->id,
            'full_name' => 'Nome Atualizado',
            'status' => 'ativo',
        ]);

        $response->assertRedirect("/members/{$member->id}");
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'full_name' => 'Nome Atualizado',
        ]);
    }

    public function test_member_toggle_status_validates_allowed_enum_values(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $church = $this->createChurch();
        $member = $this->createMember($church, ['status' => 'ativo']);

        $this->actingAs($admin);

        // 1. Status válido: altera com sucesso
        $response = $this->patch("/members/{$member->id}/status", [
            'status' => 'disciplina',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertEquals('disciplina', $member->fresh()->status);

        // 2. Status inválido (ex: payload adulterado "admin"): REJEITADO com erro de validação
        $responseInvalid = $this->patch("/members/{$member->id}/status", [
            'status' => 'admin',
        ]);
        $responseInvalid->assertSessionHasErrors('status');
        $this->assertEquals('disciplina', $member->fresh()->status);
    }

    public function test_can_transfer_member_to_another_church(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $churchFrom = $this->createChurch(['name' => 'IEADM Origem', 'status' => 'active']);
        $churchTo = $this->createChurch(['name' => 'IEADM Destino', 'status' => 'active']);

        $member = $this->createMember($churchFrom, ['full_name' => 'Membro a Transferir', 'status' => 'ativo']);

        $this->actingAs($admin);

        $response = $this->post("/members/{$member->id}/transfer", [
            'to_church_id' => $churchTo->id,
            'reason' => 'Mudança de endereço residencial',
            'notes' => 'Carta de recomendação entregue',
        ]);

        $response->assertRedirect("/members/{$member->id}");

        // Membro deve estar na nova congregação
        $this->assertEquals($churchTo->id, $member->fresh()->church_id);

        // Registro permanente em member_transfers
        $this->assertDatabaseHas('member_transfers', [
            'member_id' => $member->id,
            'from_church_id' => $churchFrom->id,
            'to_church_id' => $churchTo->id,
            'transferred_by' => $admin->id,
            'reason' => 'Mudança de endereço residencial',
        ]);
    }

    public function test_cannot_transfer_member_to_same_church(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $church = $this->createChurch(['status' => 'active']);
        $member = $this->createMember($church);

        $this->actingAs($admin);

        $response = $this->from("/members/{$member->id}/transfer")->post("/members/{$member->id}/transfer", [
            'to_church_id' => $church->id,
            'reason' => 'Tentativa inválida',
        ]);

        $response->assertRedirect("/members/{$member->id}/transfer");
        $response->assertSessionHasErrors('to_church_id');
    }

    public function test_soft_delete_member_preserves_record_in_database(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $church = $this->createChurch();
        $member = $this->createMember($church, ['full_name' => 'Membro Soft Delete']);

        $this->actingAs($admin);

        $response = $this->delete("/members/{$member->id}");

        $response->assertRedirect('/members');
        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }
}
