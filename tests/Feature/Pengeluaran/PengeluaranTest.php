<?php

namespace Tests\Feature\Pengeluaran;

use App\Models\Pengeluaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_record_a_pengeluaran(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->post('/pengeluaran', [
            'tanggal' => '2026-08-03',
            'jumlah' => 150000,
            'keterangan' => 'Bayar listrik',
        ]);

        $response->assertRedirect('/pengeluaran');
        $this->assertDatabaseHas('pengeluarans', [
            'jumlah' => 150000,
            'keterangan' => 'Bayar listrik',
        ]);
        $this->assertSame('2026-08-03', Pengeluaran::sole()->tanggal->toDateString());
    }

    public function test_owner_can_edit_a_pengeluaran(): void
    {
        $owner = User::factory()->owner()->create();
        $pengeluaran = Pengeluaran::factory()->create([
            'jumlah' => 150000,
            'keterangan' => 'Bayar listrik',
        ]);

        $response = $this->actingAs($owner)->put("/pengeluaran/{$pengeluaran->id}", [
            'tanggal' => '2026-08-04',
            'jumlah' => 175000,
            'keterangan' => 'Bayar listrik dan air',
        ]);

        $response->assertRedirect('/pengeluaran');
        $this->assertDatabaseHas('pengeluarans', [
            'id' => $pengeluaran->id,
            'jumlah' => 175000,
            'keterangan' => 'Bayar listrik dan air',
        ]);
    }

    public function test_owner_can_delete_a_pengeluaran(): void
    {
        $owner = User::factory()->owner()->create();
        $pengeluaran = Pengeluaran::factory()->create();

        $response = $this->actingAs($owner)->delete("/pengeluaran/{$pengeluaran->id}");

        $response->assertRedirect('/pengeluaran');
        $this->assertDatabaseMissing('pengeluarans', ['id' => $pengeluaran->id]);
    }

    public function test_owner_can_view_all_pengeluaran(): void
    {
        $owner = User::factory()->owner()->create();
        Pengeluaran::factory()->create([
            'tanggal' => '2026-08-03',
            'jumlah' => 150000,
            'keterangan' => 'Bayar listrik',
        ]);
        Pengeluaran::factory()->create([
            'tanggal' => '2026-07-28',
            'jumlah' => 47500,
            'keterangan' => 'Beli kantong plastik',
        ]);

        $response = $this->actingAs($owner)->get('/pengeluaran');

        $response->assertOk();
        $response->assertSee('Bayar listrik');
        $response->assertSee('Beli kantong plastik');
        $response->assertSee('data-jumlah="150000"', escape: false);
        $response->assertSee('data-jumlah="47500"', escape: false);
        $response->assertSee('2026-08-03');
        $response->assertSee('2026-07-28');
    }

    public function test_kasir_is_blocked_from_viewing_pengeluaran(): void
    {
        $kasir = User::factory()->kasir()->create();
        Pengeluaran::factory()->create(['keterangan' => 'Bayar listrik']);

        $response = $this->actingAs($kasir)->get('/pengeluaran');

        $response->assertForbidden();
    }

    public function test_kasir_is_blocked_from_recording_pengeluaran(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->post('/pengeluaran', [
            'tanggal' => '2026-08-03',
            'jumlah' => 150000,
            'keterangan' => 'Bayar listrik',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseEmpty('pengeluarans');
    }

    public function test_kasir_is_blocked_from_editing_pengeluaran(): void
    {
        $kasir = User::factory()->kasir()->create();
        $pengeluaran = Pengeluaran::factory()->create(['keterangan' => 'Bayar listrik']);

        $response = $this->actingAs($kasir)->put("/pengeluaran/{$pengeluaran->id}", [
            'tanggal' => '2026-08-04',
            'jumlah' => 1,
            'keterangan' => 'Diubah Kasir',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('pengeluarans', [
            'id' => $pengeluaran->id,
            'keterangan' => 'Bayar listrik',
        ]);
    }

    public function test_kasir_is_blocked_from_deleting_pengeluaran(): void
    {
        $kasir = User::factory()->kasir()->create();
        $pengeluaran = Pengeluaran::factory()->create();

        $response = $this->actingAs($kasir)->delete("/pengeluaran/{$pengeluaran->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('pengeluarans', ['id' => $pengeluaran->id]);
    }

}
