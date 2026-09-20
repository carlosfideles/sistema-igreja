<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Member;
use Tests\TestCase;

class SecurityAndAuditTest extends TestCase
{
    /**
     * TESTE DE SECURITY HEADERS: Verifica presença de todos os headers de segurança HTTP.
     */
    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    }

    /**
     * TESTE DE AUDITORIA: Verifica que ações em membros geram registros de auditoria completos.
     */
    public function test_actions_generate_structured_audit_logs(): void
    {
        $admin = $this->createFirstSecretaryRegional();
        $church = $this->createChurch(['name' => 'IEADM Sede']);

        $this->actingAs($admin);

        // 1. Cadastro de Membro
        $this->post('/members', [
            'church_id' => $church->id,
            'full_name' => 'Membro Auditado',
            'status' => 'ativo',
        ]);

        $member = Member::where('full_name', 'Membro Auditado')->first();
        $this->assertNotNull($member);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'members',
            'action' => 'created',
            'entity_id' => $member->id,
            'church_id' => $church->id,
        ]);

        // 2. Alteração de Status
        $this->patch("/members/{$member->id}/status", [
            'status' => 'inativo',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'members',
            'action' => 'status_changed',
            'entity_id' => $member->id,
        ]);

        // 3. Atualização de Dados
        $this->put("/members/{$member->id}", [
            'church_id' => $church->id,
            'full_name' => 'Membro Auditado Nome Novo',
            'status' => 'inativo',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'members',
            'action' => 'updated',
            'entity_id' => $member->id,
        ]);
    }
}
