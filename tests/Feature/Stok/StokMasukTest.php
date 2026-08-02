<?php

namespace Tests\Feature\Stok;

use App\Enums\TipePergerakanStok;
use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StokMasukTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_record_stok_masuk_for_barang(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create();

        $response = $this->actingAs($owner)->post("/barang/{$barang->id}/stok-masuk", [
            'jumlah' => 20,
            'tanggal' => '2026-08-01',
            'keterangan' => 'Restock awal bulan',
        ]);

        $response->assertRedirect("/barang/{$barang->id}/pergerakan-stok");
        $this->assertDatabaseHas('pergerakan_stoks', [
            'barang_id' => $barang->id,
            'tipe' => TipePergerakanStok::Masuk->value,
            'jumlah' => 20,
            'keterangan' => 'Restock awal bulan',
        ]);
        $this->assertSame(
            '2026-08-01',
            $barang->pergerakanStoks()->sole()->tanggal->toDateString(),
        );
    }

    public function test_keterangan_is_optional_when_recording_stok_masuk(): void
    {
        $owner = User::factory()->owner()->create();
        $barang = Barang::factory()->create();

        $response = $this->actingAs($owner)->post("/barang/{$barang->id}/stok-masuk", [
            'jumlah' => 5,
            'tanggal' => '2026-08-01',
        ]);

        $response->assertRedirect("/barang/{$barang->id}/pergerakan-stok");
        $this->assertDatabaseHas('pergerakan_stoks', [
            'barang_id' => $barang->id,
            'jumlah' => 5,
            'keterangan' => null,
        ]);
    }

    public function test_kasir_is_blocked_from_recording_stok_masuk(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create();

        $response = $this->actingAs($kasir)->post("/barang/{$barang->id}/stok-masuk", [
            'jumlah' => 20,
            'tanggal' => '2026-08-01',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseEmpty('pergerakan_stoks');
    }

    public function test_kasir_is_blocked_from_viewing_pergerakan_stok_history(): void
    {
        $kasir = User::factory()->kasir()->create();
        $barang = Barang::factory()->create();

        $response = $this->actingAs($kasir)->get("/barang/{$barang->id}/pergerakan-stok");

        $response->assertForbidden();
    }
}
