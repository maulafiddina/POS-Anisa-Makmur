<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StokMasukController extends Controller
{
    public function store(Request $request, Barang $barang): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $barang->catatStokMasuk(
            jumlah: $data['jumlah'],
            tanggal: $data['tanggal'],
            keterangan: $data['keterangan'] ?? null,
        );

        return redirect("/barang/{$barang->id}/pergerakan-stok");
    }
}
