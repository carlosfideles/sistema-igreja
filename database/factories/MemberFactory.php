<?php

namespace Database\Factories;

use App\Models\Church;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'church_id' => Church::factory(),
            'full_name' => fake()->name(),
            'social_name' => null,
            'cpf' => fake()->numerify('###.###.###-##'),
            'rg' => fake()->numerify('####### SSP/DF'),
            'birth_date' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['M', 'F']),
            'marital_status' => fake()->randomElement(['solteiro', 'casado', 'viuvo', 'divorciado', 'uniao_estavel']),
            'phone' => fake()->cellphoneNumber(),
            'whatsapp' => fake()->cellphoneNumber(),
            'email' => fake()->safeEmail(),
            'zip_code' => fake()->numerify('#####-###'),
            'address' => fake()->streetAddress(),
            'number' => fake()->buildingNumber(),
            'complement' => 'Casa ' . fake()->numberBetween(1, 10),
            'neighborhood' => 'Setor Residencial',
            'city' => 'Brasília',
            'state' => 'DF',
            'photo' => null,
            'entry_date' => fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'status' => 'ativo',
            'notes' => fake()->sentence(),
        ];
    }
}
