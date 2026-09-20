<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password123'),
            'cpf' => fake()->numerify('###.###.###-##'),
            'phone' => fake()->cellphoneNumber(),
            'photo' => null,
            'status' => 'active',
            'role_id' => Role::factory(),
            'scope' => 'LOCAL',
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Define o escopo como REGIONAL.
     */
    public function regional(): static
    {
        return $this->state(fn (array $attributes) => [
            'scope' => 'REGIONAL',
        ]);
    }

    /**
     * Define o escopo como LOCAL.
     */
    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'scope' => 'LOCAL',
        ]);
    }

    /**
     * Define como status inativo.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Define como status bloqueado.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'blocked',
        ]);
    }
}
