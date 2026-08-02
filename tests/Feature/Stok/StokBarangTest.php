<?php

namespace Tests\Feature\Stok;

use App\Models\Barang;
use App\Models\PergerakanStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StokBarangTest extends TestCase
{
    use RefreshDatabase;

    public function test_barang_without_pergerakan_stok_has_zero_stok(): void
    {
        $owner = User::factory()->owner()->create();
        Barang::factory()->create(['nama' => 'Teh Botol']);

        $response = $this->actingAs($owner)->get('/barang');

        $response->assertOk();
        $this->assertStokTampil($response, 0);
    }

    public function test_stok_masuk_increases_the_barang_stok_shown_on_the_list(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create(['nama' => 'Teh Botol']);

        $this->actingAs($owner)->post("/barang/{$barang->id}/stok-masuk", [
            'jumlah' => 20,
            'tanggal' => '2026-08-01',
        ]);
        $this->actingAs($owner)->post("/barang/{$barang->id}/stok-masuk", [
            'jumlah' => 5,
            'tanggal' => '2026-08-02',
        ]);

        $response = $this->actingAs($owner)->get('/barang');

        $response->assertOk();
        $this->assertStokTampil($response, 25);
    }

    public function test_stok_is_derived_from_masuk_minus_keluar(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create(['nama' => 'Teh Botol']);
        PergerakanStok::factory()->masuk(30)->create(['barang_id' => $barang->id]);
        PergerakanStok::factory()->keluar(12)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($owner)->get('/barang');

        $response->assertOk();
        $this->assertStokTampil($response, 18);
    }

    public function test_barang_with_stok_below_stok_minimum_is_flagged(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create([
            'nama' => 'Teh Botol',
            'stok_minimum' => 10,
        ]);
        PergerakanStok::factory()->masuk(3)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($owner)->get('/barang');

        $response->assertOk();
        $response->assertSee('Stok menipis');
    }

    public function test_barang_with_stok_equal_to_stok_minimum_is_flagged(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create([
            'nama' => 'Teh Botol',
            'stok_minimum' => 10,
        ]);
        PergerakanStok::factory()->masuk(10)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($owner)->get('/barang');

        $response->assertOk();
        $response->assertSee('Stok menipis');
    }

    public function test_barang_with_stok_above_stok_minimum_is_not_flagged(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create([
            'nama' => 'Teh Botol',
            'stok_minimum' => 10,
        ]);
        PergerakanStok::factory()->masuk(11)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($owner)->get('/barang');

        $response->assertOk();
        $response->assertDontSee('Stok menipis');
    }

    public function test_kasir_can_view_current_stok_on_the_barang_list(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create([
            'nama' => 'Teh Botol',
            'stok_minimum' => 10,
        ]);
        PergerakanStok::factory()->masuk(25)->create(['barang_id' => $barang->id]);

        $response = $this->actingAs($kasir)->get('/barang');

        $response->assertOk();
        $this->assertStokTampil($response, 25);
    }

    /**
     * Menyasar sel Stok secara spesifik — mencocokkan angka di mana pun pada
     * halaman bisa lolos hanya karena digit harga jual atau token CSRF.
     */
    private function assertStokTampil(TestResponse $response, int $stok): void
    {
        $response->assertSee('data-stok="'.$stok.'"', escape: false);
    }
}
