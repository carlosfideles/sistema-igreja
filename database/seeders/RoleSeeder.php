<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => '1º Secretário',
                'slug' => 'primeiro_secretario',
                'description' => 'Autoridade executiva máxima da secretaria, com permissões plenas e acesso total.',
                'level' => 1,
            ],
            [
                'name' => '2º Secretário',
                'slug' => 'segundo_secretario',
                'description' => 'Segunda autoridade da secretaria, auxilia na administração conforme permissões atribuídas.',
                'level' => 2,
            ],
            [
                'name' => '3º Secretário',
                'slug' => 'terceiro_secretario',
                'description' => 'Terceira autoridade da secretaria, atua no suporte operacional e cadastros.',
                'level' => 3,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }
    }
}
