<?php

namespace Database\Factories;

use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Penjualan>
 */
class PenjualanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kasir_id' => User::factory()->kasir(),
            'tanggal' => fake()->date(),
            'total' => fake()->numberBetween(1000, 500000),
        ];
    }
}
