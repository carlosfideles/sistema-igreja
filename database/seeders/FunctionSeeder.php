<?php

namespace Database\Seeders;

use App\Models\FunctionModel;
use Illuminate\Database\Seeder;

class FunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $functions = [
            '1º Secretário',
            '2º Secretário',
            'Tesoureiro',
            'Líder de Jovens',
            'Regente',
            'Líder de Louvor',
            'Líder de Crianças',
            'Líder de Missões',
            'Professor(a) da EBD',
            'Líder de Casais',
        ];

        foreach ($functions as $name) {
            FunctionModel::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
