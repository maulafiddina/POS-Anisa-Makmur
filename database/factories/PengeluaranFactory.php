<?php

namespace Database\Factories;

use App\Models\Pengeluaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengeluaran>
 */
class PengeluaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tanggal' => fake()->date(),
            'jumlah' => fake()->numberBetween(10000, 1000000),
            'keterangan' => fake()->sentence(3),
        ];
    }
}
