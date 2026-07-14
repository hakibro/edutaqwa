<?php

namespace Database\Factories;

use App\Models\Lembaga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lembaga>
 */
class LembagaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'yayasan_id' => \App\Models\Yayasan::factory(),
            'nama' => fake()->unique()->company(),
            'kode' => strtoupper(fake()->unique()->lexify('???')),
            'npsn' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'alamat' => fake()->address(),
            'telp' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'tingkat' => fake()->randomElement(['PAUD', 'RA', 'MI', 'MTS', 'MA', 'SD', 'SMP', 'SMA', 'SMK']),
            'is_active' => true,
        ];
    }
}

