<?php

namespace Tests;

use App\Models\Church;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';

        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

        \Illuminate\Support\Facades\Cache::flush();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        // Garante que papéis e permissões estejam sempre disponíveis nos testes
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    /**
     * Cria o 1º Secretário Regional (Administrador Máximo).
     */
    protected function createFirstSecretaryRegional(array $attributes = []): User
    {
        $role = Role::where('slug', 'primeiro_secretario')->first();

        return User::factory()->create(array_merge([
            'name' => 'Carlos Regional',
            'email' => 'carlos@ieadmdf.org.br',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'scope' => 'REGIONAL',
            'status' => 'active',
        ], $attributes));
    }

    /**
     * Cria um 2º Secretário Regional.
     */
    protected function createSecondSecretaryRegional(array $attributes = []): User
    {
        $role = Role::where('slug', 'segundo_secretario')->first();

        return User::factory()->create(array_merge([
            'name' => 'João 2º Regional',
            'email' => 'joao@ieadmdf.org.br',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'scope' => 'REGIONAL',
            'status' => 'active',
        ], $attributes));
    }

    /**
     * Cria um Secretário Local vinculado a congregações específicas.
     */
    protected function createSecretaryLocal(array $attributes = [], array $churches = []): User
    {
        $role = Role::where('slug', 'primeiro_secretario')->first();

        $user = User::factory()->create(array_merge([
            'name' => 'Pedro Local',
            'email' => 'pedro@ieadmdf.org.br',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'scope' => 'LOCAL',
            'status' => 'active',
        ], $attributes));

        if (!empty($churches)) {
            $churchIds = array_map(fn ($c) => $c instanceof Church ? $c->id : $c, $churches);
            $user->churches()->sync($churchIds);
        }

        return $user;
    }

    /**
     * Cria uma congregação de teste.
     */
    protected function createChurch(array $attributes = []): Church
    {
        return Church::factory()->create($attributes);
    }

    /**
     * Cria um membro associado a uma congregação.
     */
    protected function createMember(Church $church, array $attributes = []): Member
    {
        return Member::factory()->create(array_merge([
            'church_id' => $church->id,
        ], $attributes));
    }
}
