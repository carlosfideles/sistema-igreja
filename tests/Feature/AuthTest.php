<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('SISTEMA DE GESTÃO IEADM-DF');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = $this->createFirstSecretaryRegional();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = $this->createFirstSecretaryRegional();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = $this->createFirstSecretaryRegional(['status' => 'inactive']);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_blocked_users_cannot_authenticate(): void
    {
        $user = $this->createFirstSecretaryRegional(['status' => 'blocked']);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_login_rate_limiting_blocks_after_five_failed_attempts(): void
    {
        $user = $this->createFirstSecretaryRegional();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // A 6ª tentativa deve ser bloqueada pelo Rate Limiter
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createFirstSecretaryRegional();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
