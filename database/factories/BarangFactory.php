<?php

namespace Database\Factories;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Barang>
 */
class BarangFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kategori_id' => Kategori::factory(),
            'nama' => fake()->words(2, true),
            'harga_jual' => fake()->numberBetween(1000, 100000),
            'stok_minimum' => fake()->numberBetween(0, 20),
        ];
    }
}
