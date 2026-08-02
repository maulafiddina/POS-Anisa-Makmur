<?php

namespace Tests\Feature\Stok;

use App\Models\Barang;
use App\Models\PergerakanStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PergerakanStokTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_full_pergerakan_stok_history_for_a_barang(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create(['nama' => 'Teh Botol']);
        PergerakanStok::factory()->masuk(30)->create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-08-01',
            'keterangan' => 'Restock awal bulan',
        ]);
        PergerakanStok::factory()->keluar(12)->create([
            'barang_id' => $barang->id,
            'tanggal' => '2026-08-05',
            'keterangan' => 'Terjual',
        ]);

        $response = $this->actingAs($owner)->get("/barang/{$barang->id}/pergerakan-stok");

        $response->assertOk();
        $response->assertSee('Teh Botol');
        $response->assertSee('Restock awal bulan');
        $response->assertSee('Terjual');
        $response->assertSee('30');
        $response->assertSee('12');
        $response->assertSee('Stok Masuk');
        $response->assertSee('Stok Keluar');
    }

    public function test_history_only_shows_pergerakan_stok_of_the_requested_barang(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create();
        $barangLain = Barang::factory()->create();
        PergerakanStok::factory()->masuk(30)->create([
            'barang_id' => $barang->id,
            'keterangan' => 'Milik barang ini',
        ]);
        PergerakanStok::factory()->masuk(99)->create([
            'barang_id' => $barangLain->id,
            'keterangan' => 'Milik barang lain',
        ]);

        $response = $this->actingAs($owner)->get("/barang/{$barang->id}/pergerakan-stok");

        $response->assertOk();
        $response->assertSee('Milik barang ini');
        $response->assertDontSee('Milik barang lain');
    }
}
