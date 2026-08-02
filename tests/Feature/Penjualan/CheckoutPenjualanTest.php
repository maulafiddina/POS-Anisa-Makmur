<?php

namespace Tests\Feature\Penjualan;

use App\Enums\TipePergerakanStok;
use App\Models\Barang;
use App\Models\PergerakanStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPenjualanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Form keranjang merender beberapa baris kosong; baris yang tidak diisi
     * ikut ter-POST sebagai string kosong dan tidak boleh menggagalkan Penjualan.
     */
    public function test_unfilled_cart_rows_are_ignored(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => (string) $barang->id, 'jumlah' => '2'],
                ['barang_id' => '', 'jumlah' => ''],
                ['barang_id' => '', 'jumlah' => ''],
                ['barang_id' => '', 'jumlah' => ''],
                ['barang_id' => '', 'jumlah' => ''],
            ],
        ]);

        $response->assertRedirect('/penjualan');
        $this->assertDatabaseCount('item_penjualans', 1);
        $this->assertDatabaseHas('penjualans', ['total' => 10000]);
    }

    public function test_a_penjualan_with_no_filled_rows_is_rejected(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => '', 'jumlah' => ''],
                ['barang_id' => '', 'jumlah' => ''],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseEmpty('penjualans');
    }

    public function test_kasir_can_complete_a_penjualan_for_one_barang(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 2],
            ],
        ]);

        $response->assertRedirect('/penjualan');
        $this->assertDatabaseHas('penjualans', [
            'kasir_id' => $kasir->id,
            'total' => 10000,
        ]);
        $this->assertDatabaseHas('item_penjualans', [
            'barang_id' => $barang->id,
            'jumlah' => 2,
            'harga_jual' => 5000,
            'subtotal' => 10000,
        ]);
    }

    public function test_completing_a_penjualan_reduces_stok_via_pergerakan_stok(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $barang->id]);

        $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 2],
            ],
        ]);

        $this->assertDatabaseHas('pergerakan_stoks', [
            'barang_id' => $barang->id,
            'tipe' => TipePergerakanStok::Keluar->value,
            'jumlah' => 2,
        ]);
        $this->assertSame(8, $barang->fresh()->stok());
    }

    public function test_penjualan_total_is_the_sum_of_its_item_subtotals(): void
    {
        $kasir = User::factory()->kasir()->create();
        $teh = Barang::factory()->create(['harga_jual' => 5000]);
        $kopi = Barang::factory()->create(['harga_jual' => 7500]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $teh->id]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $kopi->id]);

        $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $teh->id, 'jumlah' => 2],
                ['barang_id' => $kopi->id, 'jumlah' => 3],
            ],
        ]);

        $this->assertDatabaseHas('item_penjualans', [
            'barang_id' => $teh->id,
            'jumlah' => 2,
            'subtotal' => 10000,
        ]);
        $this->assertDatabaseHas('item_penjualans', [
            'barang_id' => $kopi->id,
            'jumlah' => 3,
            'subtotal' => 22500,
        ]);
        $this->assertDatabaseHas('penjualans', ['total' => 32500]);
        $this->assertSame(8, $teh->fresh()->stok());
        $this->assertSame(7, $kopi->fresh()->stok());
    }

    public function test_item_records_the_harga_jual_at_the_time_of_sale(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $barang->id]);

        $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 2],
            ],
        ]);

        $barang->update(['harga_jual' => 9000]);

        $this->assertDatabaseHas('item_penjualans', [
            'barang_id' => $barang->id,
            'harga_jual' => 5000,
            'subtotal' => 10000,
        ]);
        $this->assertDatabaseHas('penjualans', ['total' => 10000]);
    }

    public function test_the_same_barang_listed_twice_is_combined_into_one_item(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $barang->id]);

        $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 2],
                ['barang_id' => $barang->id, 'jumlah' => 3],
            ],
        ]);

        $this->assertDatabaseCount('item_penjualans', 1);
        $this->assertDatabaseHas('item_penjualans', [
            'barang_id' => $barang->id,
            'jumlah' => 5,
            'subtotal' => 25000,
        ]);
        $this->assertSame(5, $barang->fresh()->stok());
    }
}
