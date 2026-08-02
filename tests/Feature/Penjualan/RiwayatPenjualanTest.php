<?php

namespace Tests\Feature\Penjualan;

use App\Models\Barang;
use App\Models\PergerakanStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPenjualanTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_only_sees_their_own_penjualan(): void
    {
        $kasir = User::factory()->kasir()->create();
        $kasirLain = User::factory()->kasir()->create();
        $this->buatPenjualan($kasir, hargaJual: 5000, jumlah: 2);
        $this->buatPenjualan($kasirLain, hargaJual: 7000, jumlah: 1);

        $response = $this->actingAs($kasir)->get('/penjualan');

        $response->assertOk();
        $response->assertSee('data-total="10000"', escape: false);
        $response->assertDontSee('data-total="7000"', escape: false);
    }

    public function test_owner_sees_penjualan_from_every_kasir(): void
    {
        $owner = User::factory()->owner()->create();
        $kasir = User::factory()->kasir()->create();
        $kasirLain = User::factory()->kasir()->create();
        $this->buatPenjualan($kasir, hargaJual: 5000, jumlah: 2);
        $this->buatPenjualan($kasirLain, hargaJual: 7000, jumlah: 1);

        $response = $this->actingAs($owner)->get('/penjualan');

        $response->assertOk();
        $response->assertSee('data-total="10000"', escape: false);
        $response->assertSee('data-total="7000"', escape: false);
    }

    public function test_owner_can_complete_a_penjualan_themselves(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($owner)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 2],
            ],
        ]);

        $response->assertRedirect('/penjualan');
        $this->assertDatabaseHas('penjualans', [
            'kasir_id' => $owner->id,
            'total' => 10000,
        ]);
    }

    private function buatPenjualan(User $kasir, int $hargaJual, int $jumlah): void
    {
        $barang = Barang::factory()->create(['harga_jual' => $hargaJual]);
        PergerakanStok::factory()->masuk(100)->create(['barang_id' => $barang->id]);

        $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => $jumlah],
            ],
        ]);
    }
}
