<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class SecretaryHierarchyTest extends TestCase
{
    public function test_regional_admin_can_create_local_secretary(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $church = $this->createChurch();
        $role = Role::where('slug', 'segundo_secretario')->first();

        $this->actingAs($admin);

        $response = $this->post('/secretaries', [
            'name' => 'Novo Secretário Local',
            'email' => 'novolocal@ieadmdf.org.br',
            'password' => 'secret12345',
            'role_id' => $role->id,
            'scope' => 'LOCAL',
            'status' => 'active',
            'churches' => [$church->id],
        ]);

        $response->assertRedirect('/secretaries');
        $this->assertDatabaseHas('users', [
            'email' => 'novolocal@ieadmdf.org.br',
            'scope' => 'LOCAL',
        ]);
    }

    public function test_local_secretary_cannot_create_regional_user(): void
    {
        $church = $this->createChurch();
        $localUser = $this->createSecretaryLocal([], [$church]);
        $role = Role::where('slug', 'terceiro_secretario')->first();

        $this->actingAs($localUser);

        $response = $this->from('/secretaries/create')->post('/secretaries', [
            'name' => 'Tentativa Hacker Regional',
            'email' => 'hacker@ieadmdf.org.br',
            'password' => 'secret12345',
            'role_id' => $role->id,
            'scope' => 'REGIONAL',
            'status' => 'active',
        ]);

        // Secretário local é bloqueado tanto pela Policy (403) quanto pelo Form Request
        $this->assertDatabaseMissing('users', ['email' => 'hacker@ieadmdf.org.br']);
    }

    public function test_first_secretary_regional_is_immutable_and_cannot_be_deleted_or_blocked(): void
    {
        $firstSecretary = $this->createFirstSecretaryRegional(['email' => 'admin.supremo@ieadmdf.org.br']);
        $secondSecretary = $this->createSecondSecretaryRegional();

        $this->actingAs($secondSecretary);

        // 1. Tentar alterar status do 1º Secretário Regional: BLOQUEADO
        $responseStatus = $this->patch("/secretaries/{$firstSecretary->id}/status", [
            'status' => 'blocked',
        ]);
        $this->assertEquals('active', $firstSecretary->fresh()->status);

        // 2. Tentar excluir o 1º Secretário Regional: BLOQUEADO
        $responseDelete = $this->delete("/secretaries/{$firstSecretary->id}");
        $this->assertNotSoftDeleted('users', ['id' => $firstSecretary->id]);
    }

    public function test_users_cannot_block_or_delete_themselves(): void
    {
        $secondSecretary = $this->createSecondSecretaryRegional();
        $this->actingAs($secondSecretary);

        // 1. Tentar alterar o próprio status: BLOQUEADO
        $this->patch("/secretaries/{$secondSecretary->id}/status", [
            'status' => 'blocked',
        ]);
        $this->assertEquals('active', $secondSecretary->fresh()->status);

        // 2. Tentar auto-exclusão: BLOQUEADO
        $this->delete("/secretaries/{$secondSecretary->id}");
        $this->assertNotSoftDeleted('users', ['id' => $secondSecretary->id]);
    }
}
