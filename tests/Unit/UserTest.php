<?php

namespace Tests\Unit;

use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_scope_helper_methods(): void
    {
        $role = Role::where('slug', 'primeiro_secretario')->first();

        $regionalUser = User::factory()->create([
            'role_id' => $role->id,
            'scope' => 'REGIONAL',
        ]);

        $localUser = User::factory()->create([
            'role_id' => $role->id,
            'scope' => 'LOCAL',
        ]);

        $this->assertTrue($regionalUser->isRegional());
        $this->assertFalse($regionalUser->isLocal());

        $this->assertTrue($localUser->isLocal());
        $this->assertFalse($localUser->isRegional());
    }

    public function test_can_access_church_logic(): void
    {
        $role = Role::where('slug', 'primeiro_secretario')->first();
        $churchA = Church::factory()->create();
        $churchB = Church::factory()->create();

        $regionalUser = User::factory()->create([
            'role_id' => $role->id,
            'scope' => 'REGIONAL',
        ]);

        $localUser = User::factory()->create([
            'role_id' => $role->id,
            'scope' => 'LOCAL',
        ]);
        $localUser->churches()->attach($churchA->id);

        // Usuário Regional acessa ambas
        $this->assertTrue($regionalUser->canAccessChurch($churchA->id));
        $this->assertTrue($regionalUser->canAccessChurch($churchB->id));

        // Usuário Local acessa apenas Igreja A
        $this->assertTrue($localUser->canAccessChurch($churchA->id));
        $this->assertFalse($localUser->canAccessChurch($churchB->id));
    }
}
