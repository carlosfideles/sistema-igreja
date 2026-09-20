<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $primeiroSecretarioRole = Role::where('slug', 'primeiro_secretario')->first();

        User::firstOrCreate(
            ['email' => 'admin@ieadm-df.org.br'],
            [
                'name' => 'Carlos Fideles',
                'email' => 'admin@ieadm-df.org.br',
                'password' => Hash::make('Admin@IEADM2026'),
                'cpf' => '000.000.000-00',
                'phone' => '(61) 99999-9999',
                'photo' => null,
                'status' => 'active',
                'role_id' => $primeiroSecretarioRole?->id,
                'scope' => 'REGIONAL',
                'last_login_at' => null,
            ]
        );
    }
}
