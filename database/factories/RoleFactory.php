<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => '1º Secretário',
            'slug' => 'primeiro_secretario',
            'description' => 'Papel padrão de secretário',
            'level' => 1,
        ];
    }

    /**
     * Define como 2º Secretário.
     */
    public function secondSecretary(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '2º Secretário',
            'slug' => 'segundo_secretario',
            'description' => '2º Secretário',
            'level' => 2,
        ]);
    }

    /**
     * Define como 3º Secretário.
     */
    public function thirdSecretary(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '3º Secretário',
            'slug' => 'terceiro_secretario',
            'description' => '3º Secretário',
            'level' => 3,
        ]);
    }
}
