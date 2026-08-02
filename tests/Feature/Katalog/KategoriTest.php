<?php

namespace Tests\Feature\Katalog;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_kategori(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->post('/kategori', [
            'nama' => 'Minuman',
        ]);

        $response->assertRedirect('/kategori');
        $this->assertDatabaseHas('kategoris', ['nama' => 'Minuman']);
    }

    public function test_owner_can_edit_kategori(): void
    {
        $owner = User::factory()->owner()->create();
        $kategori = Kategori::factory()->create(['nama' => 'Minuman']);

        $response = $this->actingAs($owner)->put("/kategori/{$kategori->id}", [
            'nama' => 'Minuman Dingin',
        ]);

        $response->assertRedirect('/kategori');
        $this->assertDatabaseHas('kategoris', [
            'id' => $kategori->id,
            'nama' => 'Minuman Dingin',
        ]);
    }

    public function test_owner_can_delete_kategori(): void
    {
        $owner = User::factory()->owner()->create();
        $kategori = Kategori::factory()->create();

        $response = $this->actingAs($owner)->delete("/kategori/{$kategori->id}");

        $response->assertRedirect('/kategori');
        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
    }

    public function test_kategori_still_referenced_by_barang_cannot_be_deleted(): void
    {
        $owner = User::factory()->owner()->create();
        $kategori = Kategori::factory()->create();
        Barang::factory()->create(['kategori_id' => $kategori->id]);

        $response = $this->actingAs($owner)->delete("/kategori/{$kategori->id}");

        $response->assertSessionHasErrors('kategori');
        $this->assertDatabaseHas('kategoris', ['id' => $kategori->id]);
    }

    public function test_kasir_is_blocked_from_viewing_kategori(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/kategori');

        $response->assertForbidden();
    }

    public function test_kasir_is_blocked_from_creating_kategori(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->post('/kategori', [
            'nama' => 'Minuman',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('kategoris', ['nama' => 'Minuman']);
    }

    public function test_kasir_is_blocked_from_editing_kategori(): void
    {
        $kasir = User::factory()->kasir()->create();
        $kategori = Kategori::factory()->create(['nama' => 'Minuman']);

        $response = $this->actingAs($kasir)->put("/kategori/{$kategori->id}", [
            'nama' => 'Diubah Kasir',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('kategoris', [
            'id' => $kategori->id,
            'nama' => 'Minuman',
        ]);
    }

    public function test_kasir_is_blocked_from_deleting_kategori(): void
    {
        $kasir = User::factory()->kasir()->create();
        $kategori = Kategori::factory()->create();

        $response = $this->actingAs($kasir)->delete("/kategori/{$kategori->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('kategoris', ['id' => $kategori->id]);
    }
}
