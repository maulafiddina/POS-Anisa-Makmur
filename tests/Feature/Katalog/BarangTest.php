<?php

namespace Tests\Feature\Katalog;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_barang(): void
    {
        $owner = User::factory()->owner()->create();
        $kategori = Kategori::factory()->create();

        $response = $this->actingAs($owner)->post('/barang', [
            'nama' => 'Teh Botol',
            'kategori_id' => $kategori->id,
            'harga_jual' => 5000,
            'stok_minimum' => 10,
        ]);

        $response->assertRedirect('/barang');
        $this->assertDatabaseHas('barangs', [
            'nama' => 'Teh Botol',
            'kategori_id' => $kategori->id,
            'harga_jual' => 5000,
            'stok_minimum' => 10,
        ]);
    }

    public function test_owner_can_edit_barang(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create(['harga_jual' => 5000]);
        $kategoriBaru = Kategori::factory()->create();

        $response = $this->actingAs($owner)->put("/barang/{$barang->id}", [
            'nama' => 'Teh Botol Dingin',
            'kategori_id' => $kategoriBaru->id,
            'harga_jual' => 6000,
            'stok_minimum' => 5,
        ]);

        $response->assertRedirect('/barang');
        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'nama' => 'Teh Botol Dingin',
            'kategori_id' => $kategoriBaru->id,
            'harga_jual' => 6000,
            'stok_minimum' => 5,
        ]);
    }

    public function test_owner_can_delete_barang(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create();

        $response = $this->actingAs($owner)->delete("/barang/{$barang->id}");

        $response->assertRedirect('/barang');
        $this->assertDatabaseMissing('barangs', ['id' => $barang->id]);
    }

    public function test_owner_can_view_barang_list_with_kategori_and_harga_jual(): void
    {
        $owner = User::factory()->owner()->create();
        $kategori = Kategori::factory()->create(['nama' => 'Minuman']);
        $barang = Barang::factory()->create([
            'nama' => 'Teh Botol',
            'kategori_id' => $kategori->id,
            'harga_jual' => 5000,
        ]);

        $response = $this->actingAs($owner)->get('/barang');

        $response->assertOk();
        $response->assertSee('Teh Botol');
        $response->assertSee('Minuman');
        $response->assertSee('5000');
        $response->assertSee('Tambah Barang');
        $response->assertSee("/barang/{$barang->id}");
    }

    public function test_kasir_can_view_barang_list_but_sees_no_edit_or_delete_affordance(): void
    {
        $kasir = User::factory()->kasir()->create();
        $kategori = Kategori::factory()->create(['nama' => 'Minuman']);
        $barang = Barang::factory()->create([
            'nama' => 'Teh Botol',
            'kategori_id' => $kategori->id,
            'harga_jual' => 5000,
        ]);

        $response = $this->actingAs($kasir)->get('/barang');

        $response->assertOk();
        $response->assertSee('Teh Botol');
        $response->assertSee('Minuman');
        $response->assertSee('5000');
        $response->assertDontSee('Tambah Barang');
        $response->assertDontSee("/barang/{$barang->id}");
    }

    public function test_kasir_is_blocked_from_creating_barang(): void
    {
        $kasir = User::factory()->kasir()->create();
        $kategori = Kategori::factory()->create();

        $response = $this->actingAs($kasir)->post('/barang', [
            'nama' => 'Teh Botol',
            'kategori_id' => $kategori->id,
            'harga_jual' => 5000,
            'stok_minimum' => 10,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('barangs', ['nama' => 'Teh Botol']);
    }

    public function test_kasir_is_blocked_from_editing_barang(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create(['nama' => 'Teh Botol']);

        $response = $this->actingAs($kasir)->put("/barang/{$barang->id}", [
            'nama' => 'Diubah Kasir',
            'kategori_id' => $barang->kategori_id,
            'harga_jual' => 9999,
            'stok_minimum' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'nama' => 'Teh Botol',
        ]);
    }

    public function test_kasir_is_blocked_from_deleting_barang(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create();

        $response = $this->actingAs($kasir)->delete("/barang/{$barang->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('barangs', ['id' => $barang->id]);
    }
}
