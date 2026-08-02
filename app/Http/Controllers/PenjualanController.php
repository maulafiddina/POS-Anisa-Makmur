<?php

namespace App\Http\Controllers;

use App\Exceptions\StokTidakCukup;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Services\PencatatanPenjualan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        $penjualans = Penjualan::with('kasir')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // Kasir hanya melihat Penjualan miliknya sendiri; Owner melihat semuanya.
        if (! $request->user()->isOwner()) {
            $penjualans->where('kasir_id', $request->user()->id);
        }

        return view('penjualan.index', [
            'penjualans' => $penjualans->get(),
            'barangs' => Barang::withStok()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request, PencatatanPenjualan $pencatatanPenjualan): RedirectResponse
    {
        $request->merge(['items' => $this->barisTerisi($request->input('items', []))]);

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.barang_id' => ['required', 'exists:barangs,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Pilih setidaknya satu Barang.',
        ]);

        try {
            $pencatatanPenjualan->catat($request->user(), $data['tanggal'], $data['items']);
        } catch (StokTidakCukup $e) {
            throw ValidationException::withMessages(['items' => $e->getMessage()]);
        }

        return redirect('/penjualan');
    }

    /**
     * Form keranjang merender sejumlah baris kosong; yang tidak dipilih
     * ikut ter-POST dan harus dibuang sebelum divalidasi.
     *
     * @param  mixed  $items
     * @return array<int, mixed>
     */
    private function barisTerisi($items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(
            $items,
            fn ($item) => is_array($item) && filled($item['barang_id'] ?? null),
        ));
    }
}
