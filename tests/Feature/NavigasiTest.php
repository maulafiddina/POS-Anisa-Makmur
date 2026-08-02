<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_links_to_every_owner_only_page(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get('/penjualan');

        $response->assertOk();
        foreach (['/penjualan', '/barang', '/kategori', '/pengeluaran', '/kas', '/kasir'] as $tautan) {
            $response->assertSee('href="'.$tautan.'"', escape: false);
        }
    }

    public function test_kasir_only_sees_links_to_pages_they_may_open(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/penjualan');

        $response->assertOk();
        foreach (['/penjualan', '/barang'] as $tautan) {
            $response->assertSee('href="'.$tautan.'"', escape: false);
        }
        foreach (['/kategori', '/pengeluaran', '/kas', '/kasir'] as $tautan) {
            $response->assertDontSee('href="'.$tautan.'"', escape: false);
        }
    }
}
