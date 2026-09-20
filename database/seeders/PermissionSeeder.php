<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            [
                'name' => 'Visualizar Dashboard',
                'slug' => 'dashboard.view',
                'module' => 'dashboard',
                'description' => 'Acessar indicadores, estatísticas e visão geral do sistema.',
            ],

            // Igrejas
            [
                'name' => 'Visualizar Igrejas',
                'slug' => 'churches.view',
                'module' => 'churches',
                'description' => 'Listar e visualizar dados cadastrais das congregações autorizadas.',
            ],
            [
                'name' => 'Cadastrar Igrejas',
                'slug' => 'churches.create',
                'module' => 'churches',
                'description' => 'Adicionar novas congregações à Regional.',
            ],
            [
                'name' => 'Editar Igrejas',
                'slug' => 'churches.update',
                'module' => 'churches',
                'description' => 'Atualizar informações cadastrais e responsáveis pelas congregações.',
            ],
            [
                'name' => 'Desativar/Excluir Igrejas',
                'slug' => 'churches.delete',
                'module' => 'churches',
                'description' => 'Desativar ou remover congregações.',
            ],

            // Membros
            [
                'name' => 'Visualizar Membros',
                'slug' => 'members.view',
                'module' => 'members',
                'description' => 'Listar e consultar fichas cadastrais dos membros.',
            ],
            [
                'name' => 'Cadastrar Membros',
                'slug' => 'members.create',
                'module' => 'members',
                'description' => 'Registrar novos membros em uma congregação.',
            ],
            [
                'name' => 'Editar Membros',
                'slug' => 'members.update',
                'module' => 'members',
                'description' => 'Alterar dados cadastrais, contato e endereço de membros.',
            ],
            [
                'name' => 'Desativar/Excluir Membros',
                'slug' => 'members.delete',
                'module' => 'members',
                'description' => 'Alterar situação ou desativar membros.',
            ],
            [
                'name' => 'Transferir Membros',
                'slug' => 'members.transfer',
                'module' => 'members',
                'description' => 'Executar transferência de membros entre congregações da Regional.',
            ],

            // Secretários / Usuários
            [
                'name' => 'Visualizar Secretários',
                'slug' => 'secretaries.view',
                'module' => 'secretaries',
                'description' => 'Consultar usuários e secretários cadastrados no sistema.',
            ],
            [
                'name' => 'Cadastrar Secretários',
                'slug' => 'secretaries.create',
                'module' => 'secretaries',
                'description' => 'Criar novos acessos para secretários.',
            ],
            [
                'name' => 'Editar Secretários',
                'slug' => 'secretaries.update',
                'module' => 'secretaries',
                'description' => 'Atualizar dados, funções e permissões de secretários.',
            ],
            [
                'name' => 'Bloquear/Desativar Secretários',
                'slug' => 'secretaries.delete',
                'module' => 'secretaries',
                'description' => 'Bloquear ou desativar o acesso de secretários.',
            ],

            // Relatórios
            [
                'name' => 'Visualizar Relatórios',
                'slug' => 'reports.view',
                'module' => 'reports',
                'description' => 'Gerar e visualizar relatórios estatísticos e cadastrais.',
            ],
            [
                'name' => 'Exportar Relatórios',
                'slug' => 'reports.export',
                'module' => 'reports',
                'description' => 'Exportar relatórios em formato PDF ou Excel.',
            ],

            // Auditoria
            [
                'name' => 'Visualizar Auditoria',
                'slug' => 'audit.view',
                'module' => 'audit',
                'description' => 'Consultar logs e histórico de atividades de todos os usuários.',
            ],

            // Configurações
            [
                'name' => 'Administrar Configurações',
                'slug' => 'settings.manage',
                'module' => 'settings',
                'description' => 'Gerenciar parâmetros gerais do sistema.',
            ],
        ];

        $allPermissionIds = [];

        foreach ($permissions as $permData) {
            $permission = Permission::firstOrCreate(
                ['slug' => $permData['slug']],
                $permData
            );
            $allPermissionIds[] = $permission->id;
        }

        // Atribui todas as permissões ao 1º Secretário
        $primeiroSecretario = Role::where('slug', 'primeiro_secretario')->first();
        if ($primeiroSecretario) {
            $primeiroSecretario->permissions()->sync($allPermissionIds);
        }

        // Atribui permissões operacionais padrão ao 2º Secretário
        $segundoSecretario = Role::where('slug', 'segundo_secretario')->first();
        if ($segundoSecretario) {
            $segundoPerms = Permission::whereIn('slug', [
                'dashboard.view',
                'churches.view',
                'members.view',
                'members.create',
                'members.update',
                'members.transfer',
                'secretaries.view',
                'reports.view',
                'reports.export',
            ])->pluck('id');
            $segundoSecretario->permissions()->sync($segundoPerms);
        }

        // Atribui permissões básicas ao 3º Secretário
        $terceiroSecretario = Role::where('slug', 'terceiro_secretario')->first();
        if ($terceiroSecretario) {
            $terceiroPerms = Permission::whereIn('slug', [
                'dashboard.view',
                'churches.view',
                'members.view',
                'members.create',
                'members.update',
                'reports.view',
            ])->pluck('id');
            $terceiroSecretario->permissions()->sync($terceiroPerms);
        }
    }
}
