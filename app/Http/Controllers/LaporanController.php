<?php

namespace App\Http\Controllers;

use App\Services\RingkasanKas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LaporanController extends Controller
{
    public function penjualan(Request $request): Response
    {
        $ringkasan = $this->ringkasan($request);

        return Pdf::loadView('laporan.penjualan-pdf', ['ringkasan' => $ringkasan])
            ->download("laporan-penjualan-{$ringkasan->tanggalAwal}-sd-{$ringkasan->tanggalAkhir}.pdf");
    }

    public function pengeluaran(Request $request): Response
    {
        $ringkasan = $this->ringkasan($request);

        return Pdf::loadView('laporan.pengeluaran-pdf', ['ringkasan' => $ringkasan])
            ->download("laporan-pengeluaran-{$ringkasan->tanggalAwal}-sd-{$ringkasan->tanggalAkhir}.pdf");
    }

    private function ringkasan(Request $request): RingkasanKas
    {
        $rentang = $request->validate([
            'tanggal_awal' => ['required', 'date'],
            'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_awal'],
        ]);

        return RingkasanKas::untukRentang($rentang['tanggal_awal'], $rentang['tanggal_akhir']);
    }
}
