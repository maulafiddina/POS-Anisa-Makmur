<?php

namespace Tests\Feature\Kas;

use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_kas_as_total_penjualan_minus_total_pengeluaran(): void
    {
        $owner = User::factory()->owner()->create();
        Penjualan::factory()->create(['tanggal' => '2026-08-01', 'total' => 100000]);
        Penjualan::factory()->create(['tanggal' => '2026-08-05', 'total' => 50000]);
        Pengeluaran::factory()->create(['tanggal' => '2026-08-03', 'jumlah' => 30000]);

        $response = $this->actingAs($owner)->get('/kas?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertOk();
        $response->assertSee('data-total-penjualan="150000"', escape: false);
        $response->assertSee('data-total-pengeluaran="30000"', escape: false);
        $response->assertSee('data-kas="120000"', escape: false);
    }

    public function test_kas_ignores_penjualan_and_pengeluaran_outside_the_range(): void
    {
        $owner = User::factory()->owner()->create();
        Penjualan::factory()->create(['tanggal' => '2026-07-31', 'total' => 999000]);
        Penjualan::factory()->create(['tanggal' => '2026-08-01', 'total' => 100000]);
        Penjualan::factory()->create(['tanggal' => '2026-08-05', 'total' => 50000]);
        Penjualan::factory()->create(['tanggal' => '2026-08-06', 'total' => 777000]);
        Pengeluaran::factory()->create(['tanggal' => '2026-07-31', 'jumlah' => 888000, 'keterangan' => 'Sebelum rentang']);
        Pengeluaran::factory()->create(['tanggal' => '2026-08-03', 'jumlah' => 30000, 'keterangan' => 'Dalam rentang']);
        Pengeluaran::factory()->create(['tanggal' => '2026-08-06', 'jumlah' => 666000, 'keterangan' => 'Sesudah rentang']);

        $response = $this->actingAs($owner)->get('/kas?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertOk();
        $response->assertSee('data-kas="120000"', escape: false);
        $response->assertSee('data-total="100000"', escape: false);
        $response->assertSee('data-total="50000"', escape: false);
        $response->assertDontSee('data-total="999000"', escape: false);
        $response->assertDontSee('data-total="777000"', escape: false);
        $response->assertSee('Dalam rentang');
        $response->assertDontSee('Sebelum rentang');
        $response->assertDontSee('Sesudah rentang');
    }

    public function test_kas_page_links_to_both_pdf_exports_for_the_chosen_range(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get('/kas?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertOk();
        $response->assertSee('href="/laporan/penjualan/pdf?tanggal_awal=2026-08-01&amp;tanggal_akhir=2026-08-05"', escape: false);
        $response->assertSee('href="/laporan/pengeluaran/pdf?tanggal_awal=2026-08-01&amp;tanggal_akhir=2026-08-05"', escape: false);
    }

    public function test_kas_rejects_a_range_that_ends_before_it_starts(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get('/kas?tanggal_awal=2026-08-05&tanggal_akhir=2026-08-01');

        $response->assertSessionHasErrors('tanggal_akhir');
    }

    public function test_kas_without_a_range_shows_no_ringkasan(): void
    {
        $owner = User::factory()->owner()->create();
        Penjualan::factory()->create(['tanggal' => '2026-08-03', 'total' => 100000]);

        $response = $this->actingAs($owner)->get('/kas');

        $response->assertOk();
        $response->assertDontSee('data-kas=', escape: false);
        $response->assertSee('Pilih rentang tanggal');
    }

    public function test_kas_understands_a_range_written_without_leading_zeroes(): void
    {
        $owner = User::factory()->owner()->create();
        Penjualan::factory()->create(['tanggal' => '2026-08-03', 'total' => 100000]);
        Pengeluaran::factory()->create(['tanggal' => '2026-08-03', 'jumlah' => 30000]);

        $response = $this->actingAs($owner)->get('/kas?tanggal_awal=2026-8-1&tanggal_akhir=2026-8-31');

        $response->assertOk();
        $response->assertSee('data-kas="70000"', escape: false);
    }

    public function test_kasir_is_blocked_from_viewing_kas(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/kas?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertForbidden();
    }
}
