<?php

namespace App\Services;

use App\Exceptions\StokTidakCukup;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Mencatat satu Penjualan beserta Item Penjualan dan Pergerakan Stok-nya
 * dalam satu transaksi — semua tersimpan, atau tidak sama sekali.
 */
class PencatatanPenjualan
{
    /**
     * @param  array<int, array{barang_id: int|string, jumlah: int|string}>  $items
     *
     * @throws StokTidakCukup
     */
    public function catat(User $kasir, string $tanggal, array $items): Penjualan
    {
        $jumlahPerBarang = $this->jumlahPerBarang($items);

        return DB::transaction(function () use ($kasir, $tanggal, $jumlahPerBarang) {
            // Dikunci agar dua checkout bersamaan tidak sama-sama lolos pemeriksaan Stok.
            $barangs = Barang::whereIn('id', array_keys($jumlahPerBarang))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotalPerBarang = $this->periksaStokDanHitungSubtotal($barangs, $jumlahPerBarang);

            $penjualan = Penjualan::create([
                'kasir_id' => $kasir->id,
                'tanggal' => $tanggal,
                'total' => array_sum($subtotalPerBarang),
            ]);

            foreach ($subtotalPerBarang as $barangId => $subtotal) {
                /** @var Barang $barang */
                $barang = $barangs[$barangId];
                $jumlah = $jumlahPerBarang[$barangId];

                $itemPenjualan = $penjualan->itemPenjualans()->create([
                    'barang_id' => $barang->id,
                    'jumlah' => $jumlah,
                    'harga_jual' => $barang->harga_jual,
                    'subtotal' => $subtotal,
                ]);

                $barang->catatStokKeluar($jumlah, $tanggal, $itemPenjualan);
            }

            return $penjualan;
        });
    }

    /**
     * Seluruh Barang diperiksa sebelum satu baris pun ditulis, agar penolakan
     * tidak menyisakan Penjualan separuh jadi.
     *
     * @param  \Illuminate\Support\Collection<int, Barang>  $barangs
     * @param  array<int, int>  $jumlahPerBarang
     * @return array<int, int>
     *
     * @throws StokTidakCukup
     */
    private function periksaStokDanHitungSubtotal($barangs, array $jumlahPerBarang): array
    {
        $subtotalPerBarang = [];

        foreach ($jumlahPerBarang as $barangId => $jumlah) {
            /** @var Barang $barang */
            $barang = $barangs[$barangId];
            $tersedia = $barang->stok();

            if ($jumlah > $tersedia) {
                throw new StokTidakCukup($barang, $jumlah, $tersedia);
            }

            $subtotalPerBarang[$barangId] = $barang->harga_jual * $jumlah;
        }

        return $subtotalPerBarang;
    }

    /**
     * Barang yang sama disebut dua kali tetap satu Item Penjualan, agar
     * pemeriksaan Stok melihat jumlah keseluruhan, bukan per baris.
     *
     * @param  array<int, array{barang_id: int|string, jumlah: int|string}>  $items
     * @return array<int, int>
     */
    private function jumlahPerBarang(array $items): array
    {
        $jumlahPerBarang = [];

        foreach ($items as $item) {
            $barangId = (int) $item['barang_id'];
            $jumlahPerBarang[$barangId] = ($jumlahPerBarang[$barangId] ?? 0) + (int) $item['jumlah'];
        }

        return $jumlahPerBarang;
    }
}
