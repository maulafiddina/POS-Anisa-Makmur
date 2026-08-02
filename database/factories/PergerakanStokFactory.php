<?php

namespace Database\Factories;

use App\Enums\TipePergerakanStok;
use App\Models\Barang;
use App\Models\PergerakanStok;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PergerakanStok>
 */
class PergerakanStokFactory extends Factory
{
    public function definition(): array
    {
        return [
            'barang_id' => Barang::factory(),
            'tipe' => TipePergerakanStok::Masuk,
            'jumlah' => fake()->numberBetween(1, 50),
            'tanggal' => fake()->date(),
            'keterangan' => null,
        ];
    }

    public function masuk(int $jumlah): static
    {
        return $this->state([
            'tipe' => TipePergerakanStok::Masuk,
            'jumlah' => $jumlah,
        ]);
    }

    public function keluar(int $jumlah): static
    {
        return $this->state([
            'tipe' => TipePergerakanStok::Keluar,
            'jumlah' => $jumlah,
        ]);
    }
}
