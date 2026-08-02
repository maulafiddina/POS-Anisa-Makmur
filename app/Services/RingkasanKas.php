<?php

namespace App\Services;

use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kas diturunkan dari catatan Penjualan dan Pengeluaran pada satu rentang
 * tanggal — tidak ada tabel kas (lihat docs/adr/0001).
 */
class RingkasanKas
{
    /**
     * @param  Collection<int, Penjualan>  $penjualans
     * @param  Collection<int, Pengeluaran>  $pengeluarans
     */
    private function __construct(
        public readonly string $tanggalAwal,
        public readonly string $tanggalAkhir,
        public readonly Collection $penjualans,
        public readonly Collection $pengeluarans,
    ) {}

    public static function untukRentang(string $tanggalAwal, string $tanggalAkhir): self
    {
        // Rentang datang dari query string, jadi bentuknya belum tentu Y-m-d
        // ("2026-8-1" juga lolos validasi `date`) — sedangkan perbandingan di
        // batasiRentang() dan judul laporan mengandalkan bentuk baku.
        $tanggalAwal = Carbon::parse($tanggalAwal)->toDateString();
        $tanggalAkhir = Carbon::parse($tanggalAkhir)->toDateString();

        return new self(
            $tanggalAwal,
            $tanggalAkhir,
            Penjualan::with('kasir')
                ->tap(fn ($query) => self::batasiRentang($query, $tanggalAwal, $tanggalAkhir))
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get(),
            Pengeluaran::query()
                ->tap(fn ($query) => self::batasiRentang($query, $tanggalAwal, $tanggalAkhir))
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get(),
        );
    }

    /**
     * Kolom `tanggal` ditulis Eloquent dengan jam (00:00:00), jadi perbandingan
     * langsung terhadap string tanggal akan membuang baris di batas akhir
     * rentang — bandingkan sebagai tanggal, bukan sebagai teks.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $query
     */
    private static function batasiRentang($query, string $tanggalAwal, string $tanggalAkhir): void
    {
        $query->whereDate('tanggal', '>=', $tanggalAwal)
            ->whereDate('tanggal', '<=', $tanggalAkhir);
    }

    public function totalPenjualan(): int
    {
        return (int) $this->penjualans->sum('total');
    }

    public function totalPengeluaran(): int
    {
        return (int) $this->pengeluarans->sum('jumlah');
    }

    public function kas(): int
    {
        return $this->totalPenjualan() - $this->totalPengeluaran();
    }
}
