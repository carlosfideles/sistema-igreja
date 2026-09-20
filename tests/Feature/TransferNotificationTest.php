<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Member;
use App\Models\MemberTransfer;
use Tests\TestCase;

class TransferNotificationTest extends TestCase
{
    public function test_local_secretary_sees_unread_transfer_notification_modal(): void
    {
        $church = $this->createChurch(['name' => 'Congregação Alpha']);
        $churchDest = $this->createChurch(['name' => 'Congregação Beta']);
        $localSecretary = $this->createSecretaryLocal([], [$church]);
        $member = $this->createMember($church, ['full_name' => 'João Teste']);

        $transfer = MemberTransfer::create([
            'member_id' => $member->id,
            'from_church_id' => $church->id,
            'to_church_id' => $churchDest->id,
            'transferred_by' => $localSecretary->id,
            'transferred_at' => now(),
            'reason' => 'Mudança de bairro',
            'status' => 'approved',
            'local_notified' => false,
        ]);

        $response = $this->actingAs($localSecretary)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Atualização de Transferência');
        $response->assertSee('João Teste');
        $response->assertSee('APROVADA');
        $response->assertSee('Congregação Beta');
    }

    public function test_local_secretary_can_mark_transfer_notification_as_read(): void
    {
        $church = $this->createChurch(['name' => 'Congregação Alpha']);
        $churchDest = $this->createChurch(['name' => 'Congregação Beta']);
        $localSecretary = $this->createSecretaryLocal([], [$church]);
        $member = $this->createMember($church, ['full_name' => 'Maria Silva']);

        $transfer = MemberTransfer::create([
            'member_id' => $member->id,
            'from_church_id' => $church->id,
            'to_church_id' => $churchDest->id,
            'transferred_by' => $localSecretary->id,
            'transferred_at' => now(),
            'reason' => 'Transferência solicitada',
            'status' => 'rejected',
            'local_notified' => false,
        ]);

        $response = $this->actingAs($localSecretary)->postJson("/transfers/{$transfer->id}/mark-notified");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('member_transfers', [
            'id' => $transfer->id,
            'local_notified' => true,
        ]);

        // Next visit should not display the notification modal
        $dashboardResponse = $this->actingAs($localSecretary)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertDontSee('transfer-notification-overlay');
    }

    public function test_regional_admin_can_reject_pending_transfer(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $churchA = $this->createChurch(['name' => 'Congregação Alpha']);
        $churchB = $this->createChurch(['name' => 'Congregação Beta']);
        $localSecretary = $this->createSecretaryLocal([], [$churchA]);
        $member = $this->createMember($churchA, ['full_name' => 'Lucas Oliveira']);

        $transfer = MemberTransfer::create([
            'member_id' => $member->id,
            'from_church_id' => $churchA->id,
            'to_church_id' => $churchB->id,
            'transferred_by' => $localSecretary->id,
            'transferred_at' => now(),
            'reason' => 'Motivo pessoal',
            'status' => 'pending',
            'local_notified' => false,
        ]);

        $response = $this->actingAs($admin)->post("/transfers/{$transfer->id}/reject");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('member_transfers', [
            'id' => $transfer->id,
            'status' => 'rejected',
            'local_notified' => false,
        ]);

        // Verifica que o histórico eclesiástico NÃO é poluído por ações de transferência
        $this->assertDatabaseMissing('member_ecclesiastical_histories', [
            'member_id' => $member->id,
            'created_by' => $admin->id,
        ]);

        // Secretário local deve visualizar o modal com "RECUSADA"
        $localResponse = $this->actingAs($localSecretary)->get('/dashboard');
        $localResponse->assertStatus(200);
        $localResponse->assertSee('Atualização de Transferência');
        $localResponse->assertSee('Lucas Oliveira');
        $localResponse->assertSee('RECUSADA');
    }

    public function test_local_secretary_cannot_reject_transfer(): void
    {
        $churchA = $this->createChurch(['name' => 'Congregação Alpha']);
        $churchB = $this->createChurch(['name' => 'Congregação Beta']);
        $localSecretary = $this->createSecretaryLocal([], [$churchA]);
        $member = $this->createMember($churchA, ['full_name' => 'Lucas Oliveira']);

        $transfer = MemberTransfer::create([
            'member_id' => $member->id,
            'from_church_id' => $churchA->id,
            'to_church_id' => $churchB->id,
            'transferred_by' => $localSecretary->id,
            'transferred_at' => now(),
            'reason' => 'Motivo pessoal',
            'status' => 'pending',
            'local_notified' => false,
        ]);

        $response = $this->actingAs($localSecretary)->post("/transfers/{$transfer->id}/reject");
        $response->assertStatus(403);
    }
}
