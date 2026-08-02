<?php

namespace Tests\Feature\Penjualan;

use App\Enums\TipePergerakanStok;
use App\Models\Barang;
use App\Models\PergerakanStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OversellPenjualanTest extends TestCase
{
    use RefreshDatabase;

    public function test_selling_more_than_the_current_stok_is_rejected(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(3)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 4],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertSame(3, $barang->fresh()->stok());
    }

    public function test_a_rejected_penjualan_leaves_no_partial_state(): void
    {
        $kasir = User::factory()->kasir()->create();
        $cukup = Barang::factory()->create(['harga_jual' => 5000]);
        $kurang = Barang::factory()->create(['harga_jual' => 7500]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $cukup->id]);
        PergerakanStok::factory()->masuk(1)->create(['barang_id' => $kurang->id]);

        $response = $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $cukup->id, 'jumlah' => 2],
                ['barang_id' => $kurang->id, 'jumlah' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseEmpty('penjualans');
        $this->assertDatabaseEmpty('item_penjualans');
        $this->assertDatabaseMissing('pergerakan_stoks', [
            'tipe' => TipePergerakanStok::Keluar->value,
        ]);
        $this->assertSame(10, $cukup->fresh()->stok());
        $this->assertSame(1, $kurang->fresh()->stok());
    }

    public function test_a_rejected_penjualan_gives_the_cart_back_to_the_kasir(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(3)->create(['barang_id' => $barang->id]);

        $this->actingAs($kasir);
        $this->from('/penjualan')->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 4],
            ],
        ]);

        $response = $this->get('/penjualan');

        $response->assertOk();
        $response->assertSee('value="4"', escape: false);
        $response->assertSee('value="'.$barang->id.'" selected', escape: false);
    }

    public function test_selling_exactly_the_available_stok_is_allowed(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(3)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 3],
            ],
        ]);

        $response->assertRedirect('/penjualan');
        $this->assertSame(0, $barang->fresh()->stok());
    }

    public function test_combined_quantities_of_the_same_barang_are_checked_against_stok(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        PergerakanStok::factory()->masuk(4)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($kasir)->post('/penjualan', [
            'tanggal' => '2026-08-03',
            'items' => [
                ['barang_id' => $barang->id, 'jumlah' => 3],
                ['barang_id' => $barang->id, 'jumlah' => 3],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseEmpty('penjualans');
        $this->assertSame(4, $barang->fresh()->stok());
    }
}
