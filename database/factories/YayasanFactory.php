<?php

namespace Database\Factories;

use App\Models\Yayasan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Yayasan>
 */
class YayasanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->company() . ' Foundation',
            'kode' => strtoupper(fake()->unique()->bothify('??##')),
            'alamat' => fake()->address(),
            'telp' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'is_active' => true,
        ];
    }
}
