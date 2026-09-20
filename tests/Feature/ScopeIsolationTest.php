<?php

namespace Tests\Feature;

use App\Models\Church;
use App\Models\Member;
use Tests\TestCase;

class ScopeIsolationTest extends TestCase
{
    /**
     * TESTE CRÍTICO: Usuário Regional possui acesso a todas as congregações.
     */
    public function test_regional_user_has_automatic_access_to_all_churches(): void
    {
        $regionalUser = $this->createFirstSecretaryRegional();

        $churchA = $this->createChurch(['name' => 'IEADM Asa Sul']);
        $churchB = $this->createChurch(['name' => 'IEADM Taguatinga']);
        $churchC = $this->createChurch(['name' => 'IEADM Ceilândia']);

        $memberA = $this->createMember($churchA, ['full_name' => 'Membro Asa Sul']);
        $memberB = $this->createMember($churchB, ['full_name' => 'Membro Taguatinga']);

        $this->actingAs($regionalUser);

        // Acesso à listagem de congregações
        $response = $this->get('/churches');
        $response->assertStatus(200);
        $response->assertSee('IEADM Asa Sul');
        $response->assertSee('IEADM Taguatinga');
        $response->assertSee('IEADM Ceilândia');

        // Acesso direto aos detalhes de qualquer congregação
        $this->get("/churches/{$churchA->id}")->assertStatus(200);
        $this->get("/churches/{$churchB->id}")->assertStatus(200);
        $this->get("/churches/{$churchC->id}")->assertStatus(200);

        // Acesso à listagem e ficha de qualquer membro
        $membersResponse = $this->get('/members');
        $membersResponse->assertStatus(200);
        $membersResponse->assertSee('Membro Asa Sul');
        $membersResponse->assertSee('Membro Taguatinga');

        $this->get("/members/{$memberA->id}")->assertStatus(200);
        $this->get("/members/{$memberB->id}")->assertStatus(200);
    }

    /**
     * TESTE CRÍTICO: Usuário Regional cria nova congregação e acessa automaticamente.
     */
    public function test_regional_user_creates_new_church_and_accesses_it_immediately(): void
    {
        $regionalUser = $this->createFirstSecretaryRegional();
        $this->actingAs($regionalUser);

        $response = $this->post('/churches', [
            'name' => 'IEADM Samambaia Norte',
            'code' => 'IG-SMB-01',
            'city' => 'Samambaia',
            'state' => 'DF',
            'status' => 'active',
        ]);

        $response->assertRedirect('/churches');
        $newChurch = Church::where('code', 'IG-SMB-01')->first();
        $this->assertNotNull($newChurch);

        // Acesso imediato sem necessidade de vínculo manual
        $this->get("/churches/{$newChurch->id}")->assertStatus(200);
        $this->assertTrue($regionalUser->canAccessChurch($newChurch->id));
    }

    /**
     * TESTE CRÍTICO DE ISOLAMENTO: Usuário Local só acessa igrejas e membros autorizados.
     * Bloqueio rigoroso contra IDOR.
     */
    public function test_local_user_cannot_access_unauthorized_churches_or_their_members(): void
    {
        $churchA = $this->createChurch(['name' => 'IEADM Autorizada A']);
        $churchB = $this->createChurch(['name' => 'IEADM Proibida B']);

        // Usuário Local com acesso APENAS à Igreja A
        $localUser = $this->createSecretaryLocal(['name' => 'Pedro Local'], [$churchA]);

        $memberA = $this->createMember($churchA, ['full_name' => 'Membro Permitido']);
        $memberB = $this->createMember($churchB, ['full_name' => 'Membro Oculto']);

        $this->actingAs($localUser);

        // 1. Listagem de igrejas: deve exibir Igreja A, mas NUNCA Igreja B
        $responseChurches = $this->get('/churches');
        $responseChurches->assertStatus(200);
        $responseChurches->assertSee('IEADM Autorizada A');
        $responseChurches->assertDontSee('IEADM Proibida B');

        // 2. IDOR em Igrejas: Acesso direto via URL à Igreja A permitido; Igreja B BLOQUEADA (403)
        $this->get("/churches/{$churchA->id}")->assertStatus(200);
        $this->get("/churches/{$churchB->id}")->assertStatus(403);
        $this->get("/churches/{$churchB->id}/edit")->assertStatus(403);

        // 3. Listagem de membros: deve exibir Membro A, mas NUNCA Membro B
        $responseMembers = $this->get('/members');
        $responseMembers->assertStatus(200);
        $responseMembers->assertSee('Membro Permitido');
        $responseMembers->assertDontSee('Membro Oculto');

        // 4. IDOR em Membros: Acesso direto via URL à ficha do Membro B BLOQUEADO (403)
        $this->get("/members/{$memberA->id}")->assertStatus(200);
        $this->get("/members/{$memberB->id}")->assertStatus(403);
        $this->get("/members/{$memberB->id}/edit")->assertStatus(403);
        $this->get("/members/{$memberB->id}/print")->assertStatus(403);

        // 5. Bloqueio de alteração de status de membro de outra congregação (403)
        $this->patch("/members/{$memberB->id}/status", [
            'status' => 'inativo',
        ])->assertStatus(403);

        // 6. Bloqueio de transferência de membro de congregação não autorizada (403)
        $this->get("/members/{$memberB->id}/transfer")->assertStatus(403);
    }

    /**
     * TESTE DE ISOLAMENTO: Usuário Local não pode cadastrar membros em congregações não autorizadas.
     */
    public function test_local_user_cannot_create_member_in_unauthorized_church(): void
    {
        $churchA = $this->createChurch(['name' => 'IEADM Permitida']);
        $churchB = $this->createChurch(['name' => 'IEADM Proibida']);

        $localUser = $this->createSecretaryLocal([], [$churchA]);
        $this->actingAs($localUser);

        // Tentar cadastrar membro na Igreja B (não autorizada)
        $response = $this->from('/members/create')->post('/members', [
            'church_id' => $churchB->id,
            'full_name' => 'Tentativa Hacker',
            'status' => 'ativo',
        ]);

        $response->assertRedirect('/members/create');
        $response->assertSessionHasErrors('church_id');
        $this->assertDatabaseMissing('members', ['full_name' => 'Tentativa Hacker']);
    }

    /**
     * TESTE DE ISOLAMENTO EM RELATÓRIOS: Relatórios respeitam rigorosamente o escopo local.
     */
    public function test_reports_strictly_respect_user_scope(): void
    {
        $churchA = $this->createChurch(['name' => 'IEADM Gama']);
        $churchB = $this->createChurch(['name' => 'IEADM Sobradinho']);

        $this->createMember($churchA, ['full_name' => 'Lucas do Gama', 'status' => 'ativo']);
        $this->createMember($churchB, ['full_name' => 'Mateus de Sobradinho', 'status' => 'ativo']);

        $localUser = $this->createSecretaryLocal([], [$churchA]);
        $this->actingAs($localUser);

        $response = $this->get('/reports/members');
        $response->assertStatus(200);
        $response->assertSee('Lucas do Gama');
        $response->assertDontSee('Mateus de Sobradinho');
    }

    /**
     * TESTE DE RBAC: Secretário Local não visualiza o botão 'Editar Dados' e não pode editar congregação.
     */
    public function test_local_secretary_cannot_edit_congregation_and_button_is_hidden(): void
    {
        $church = $this->createChurch(['name' => 'IEADM Asa Norte']);
        $localUser = $this->createSecretaryLocal([], [$church]);
        $regionalUser = $this->createFirstSecretaryRegional();

        // 1. Secretário Local visualiza a congregação, mas NÃO vê o botão 'Editar Dados'
        $this->actingAs($localUser);
        $showResponse = $this->get("/churches/{$church->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('Editar Dados');

        // 2. Secretário Local é barrado com 403 ao tentar acessar a rota de edição e atualização
        $this->get("/churches/{$church->id}/edit")->assertStatus(403);
        $this->put("/churches/{$church->id}", [
            'name' => 'IEADM Nome Alterado',
            'city' => 'Brasília',
            'state' => 'DF',
        ])->assertStatus(403);

        // 3. Secretário Regional visualiza o botão e pode editar
        $this->actingAs($regionalUser);
        $regionalShowResponse = $this->get("/churches/{$church->id}");
        $regionalShowResponse->assertStatus(200);
        $regionalShowResponse->assertSee('Editar Dados');
        $this->get("/churches/{$church->id}/edit")->assertStatus(200);
    }
}
