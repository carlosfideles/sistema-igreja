<?php

namespace Database\Factories;

use App\Models\Church;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Church>
 */
class ChurchFactory extends Factory
{
    protected $model = Church::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'IEADM ' . fake()->city(),
            'code' => 'IG-' . strtoupper(fake()->unique()->bothify('???-###')),
            'cnpj' => fake()->numerify('##.###.###/0001-##'),
            'phone' => fake()->cellphoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'zip_code' => fake()->numerify('#####-###'),
            'address' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'complement' => 'Lote ' . fake()->numberBetween(1, 30),
            'neighborhood' => 'Setor ' . fake()->word(),
            'city' => fake()->randomElement(['Brasília', 'Taguatinga', 'Ceilândia', 'Samambaia', 'Gama', 'Sobradinho', 'Planaltina']),
            'state' => 'DF',
            'responsible_name' => 'Pr. ' . fake()->name('male'),
            'foundation_date' => fake()->dateTimeBetween('-20 years', '-1 years')->format('Y-m-d'),
            'status' => 'active',
            'logo' => null,
            'notes' => fake()->sentence(),
        ];
    }

    /**
     * Define o status como inativo.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
