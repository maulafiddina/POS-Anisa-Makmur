<?php

namespace App\Models;

use App\Enums\TipePergerakanStok;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kategori_id', 'nama', 'harga_jual', 'stok_minimum'])]
class Barang extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'harga_jual' => 'integer',
            'stok_minimum' => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function pergerakanStoks(): HasMany
    {
        return $this->hasMany(PergerakanStok::class);
    }

    /**
     * Stok saat ini, diturunkan dari seluruh Pergerakan Stok.
     *
     * Memakai nilai yang sudah ikut di-query oleh scope `withStok` bila ada,
     * agar daftar Barang tidak menembak satu query per baris.
     */
    public function stok(): int
    {
        return (int) ($this->attributes['stok']
            ?? $this->pergerakanStoks()->selectRaw(self::ekspresiStok().' as stok')->value('stok'));
    }

    public function stokMenipis(): bool
    {
        return $this->stok() <= $this->stok_minimum;
    }

    /**
     * Ikut menghitung Stok dalam satu query, menghindari N+1 pada daftar Barang.
     */
    public function scopeWithStok(Builder $query): void
    {
        $query->select('barangs.*')->addSelect([
            'stok' => PergerakanStok::query()
                ->selectRaw(self::ekspresiStok())
                ->whereColumn('barang_id', 'barangs.id'),
        ]);
    }

    /**
     * Setiap tipe disebut eksplisit agar tipe baru tidak diam-diam ikut mengurangi Stok.
     */
    private static function ekspresiStok(): string
    {
        return sprintf(
            "COALESCE(SUM(CASE WHEN tipe = '%s' THEN jumlah WHEN tipe = '%s' THEN -jumlah ELSE 0 END), 0)",
            TipePergerakanStok::Masuk->value,
            TipePergerakanStok::Keluar->value,
        );
    }

    public function catatStokMasuk(int $jumlah, string $tanggal, ?string $keterangan = null): PergerakanStok
    {
        return $this->catatPergerakanStok(TipePergerakanStok::Masuk, $jumlah, $tanggal, keterangan: $keterangan);
    }

    public function catatStokKeluar(int $jumlah, string $tanggal, ItemPenjualan $itemPenjualan): PergerakanStok
    {
        return $this->catatPergerakanStok(
            TipePergerakanStok::Keluar,
            $jumlah,
            $tanggal,
            itemPenjualan: $itemPenjualan,
        );
    }

    private function catatPergerakanStok(
        TipePergerakanStok $tipe,
        int $jumlah,
        string $tanggal,
        ?string $keterangan = null,
        ?ItemPenjualan $itemPenjualan = null,
    ): PergerakanStok {
        $pergerakanStok = $this->pergerakanStoks()->create([
            'tipe' => $tipe,
            'jumlah' => $jumlah,
            'tanggal' => $tanggal,
            'keterangan' => $keterangan,
            'item_penjualan_id' => $itemPenjualan?->id,
        ]);

        // Stok yang sudah ikut ter-query lewat `withStok` kini basi.
        unset($this->attributes['stok']);

        return $pergerakanStok;
    }
}
