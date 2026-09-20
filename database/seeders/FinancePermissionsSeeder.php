<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancePermissionsSeeder extends Seeder
{
    /**
     * Insere as permissões do módulo financeiro e as associa aos papéis.
     *
     * Permissões criadas:
     *   finance.view             — Visualizar extrato financeiro
     *   finance.create           — Registrar novos lançamentos
     *   finance.edit             — Editar lançamentos existentes
     *   finance.delete           — Excluir lançamentos
     *   finance.regional_confirm — Confirmar repasses e gerenciar isenções (Regional)
     *
     * Associação de papéis:
     *   level 1 (1º Secretário Regional) → TODAS as permissões
     *   level 2 (2º Secretário)          → finance.view, finance.create, finance.edit, finance.delete
     *   level 3 (3º Secretário)          → finance.view, finance.create
     */
    public function run(): void
    {
        $now = now();

        // -------------------------------------------------------
        // 1. Inserir permissões (se ainda não existirem)
        // -------------------------------------------------------
        $permissions = [
            [
                'slug'        => 'finance.view',
                'name'        => 'Visualizar Financeiro',
                'module'      => 'finance',
                'description' => 'Visualizar extrato financeiro, saldo e relatórios de congregações autorizadas.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'slug'        => 'finance.create',
                'name'        => 'Registrar Lançamento Financeiro',
                'module'      => 'finance',
                'description' => 'Registrar entradas (dízimos, ofertas, outros) e saídas/despesas.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'slug'        => 'finance.edit',
                'name'        => 'Editar Lançamento Financeiro',
                'module'      => 'finance',
                'description' => 'Editar lançamentos financeiros com rastreabilidade e log de auditoria.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'slug'        => 'finance.delete',
                'name'        => 'Excluir Lançamento Financeiro',
                'module'      => 'finance',
                'description' => 'Excluir lançamentos financeiros com auditoria completa dos valores excluídos.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'slug'        => 'finance.regional_confirm',
                'name'        => 'Confirmar Repasses e Isenções',
                'module'      => 'finance',
                'description' => 'Confirmar recebimento de repasses de 10% e gerenciar isenções de congregações (Regional).',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // -------------------------------------------------------
        // 2. Buscar IDs das permissões recém-inseridas
        // -------------------------------------------------------
        $permIds = DB::table('permissions')
            ->whereIn('slug', array_column($permissions, 'slug'))
            ->pluck('id', 'slug');

        // -------------------------------------------------------
        // 3. Buscar papéis por nível hierárquico
        // -------------------------------------------------------
        $roles = DB::table('roles')->pluck('id', 'level');

        // -------------------------------------------------------
        // 4. Associar permissões aos papéis (role_permissions)
        // -------------------------------------------------------

        // 1º Secretário Regional (level 1) → TODAS as permissões do módulo
        if (isset($roles[1])) {
            foreach ($permIds as $permId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $roles[1], 'permission_id' => $permId],
                    ['role_id' => $roles[1], 'permission_id' => $permId]
                );
            }
        }

        // 2º Secretário (level 2) → view, create, edit, delete (sem confirm regional)
        if (isset($roles[2])) {
            $level2Slugs = ['finance.view', 'finance.create', 'finance.edit', 'finance.delete'];
            foreach ($level2Slugs as $slug) {
                if (isset($permIds[$slug])) {
                    DB::table('role_permissions')->updateOrInsert(
                        ['role_id' => $roles[2], 'permission_id' => $permIds[$slug]],
                        ['role_id' => $roles[2], 'permission_id' => $permIds[$slug]]
                    );
                }
            }
        }

        // 3º Secretário (level 3) → apenas view e create
        if (isset($roles[3])) {
            $level3Slugs = ['finance.view', 'finance.create'];
            foreach ($level3Slugs as $slug) {
                if (isset($permIds[$slug])) {
                    DB::table('role_permissions')->updateOrInsert(
                        ['role_id' => $roles[3], 'permission_id' => $permIds[$slug]],
                        ['role_id' => $roles[3], 'permission_id' => $permIds[$slug]]
                    );
                }
            }
        }

        $this->command->info('✅ Permissões do módulo financeiro inseridas e associadas com sucesso!');
    }
}
