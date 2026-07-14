<?php

namespace Database\Factories;

use App\Models\Guru;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guru>
 */
class GuruFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lembaga_id' => \App\Models\Lembaga::factory(),
            'nama' => fake()->name(),
            'nip' => fake()->optional()->numerify('################'),
            'nuptk' => fake()->optional()->numerify('##############'),
            'jenis_ptk' => fake()->randomElement(['Guru Mapel', 'Guru Kelas', 'Guru BK', 'Kepala Sekolah']),
            'status_satminkal' => fake()->boolean(30),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->date(max: 'now'),
            'alamat' => fake()->address(),
            'telp' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'is_approved' => true,
            'is_active' => true,
        ];
    }
}
