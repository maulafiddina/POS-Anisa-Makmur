<?php

namespace Tests\Feature\Laporan;

use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_export_laporan_penjualan_as_pdf(): void
    {
        $owner = User::factory()->owner()->create();
        Penjualan::factory()->create(['tanggal' => '2026-08-03', 'total' => 100000]);

        $response = $this->actingAs($owner)->get('/laporan/penjualan/pdf?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_owner_can_export_laporan_pengeluaran_as_pdf(): void
    {
        $owner = User::factory()->owner()->create();
        Pengeluaran::factory()->create(['tanggal' => '2026-08-03', 'jumlah' => 30000]);

        $response = $this->actingAs($owner)->get('/laporan/pengeluaran/pdf?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_exported_pdf_is_named_after_the_chosen_range(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get('/laporan/penjualan/pdf?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertDownload('laporan-penjualan-2026-08-01-sd-2026-08-05.pdf');
    }

    public function test_export_rejects_an_incomplete_range(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get('/laporan/penjualan/pdf?tanggal_awal=2026-08-01');

        $response->assertSessionHasErrors('tanggal_akhir');
    }

    public function test_kasir_is_blocked_from_exporting_laporan_penjualan(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/laporan/penjualan/pdf?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertForbidden();
    }

    public function test_kasir_is_blocked_from_exporting_laporan_pengeluaran(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/laporan/pengeluaran/pdf?tanggal_awal=2026-08-01&tanggal_akhir=2026-08-05');

        $response->assertForbidden();
    }
}
