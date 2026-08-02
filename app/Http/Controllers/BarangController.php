<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarangController extends Controller
{
    public function index(): View
    {
        return view('barang.index', [
            'barangs' => Barang::with('kategori')->orderBy('nama')->get(),
            'kategoris' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Barang::create($this->validatedBarang($request));

        return redirect('/barang');
    }

    public function update(Request $request, Barang $barang): RedirectResponse
    {
        $barang->update($this->validatedBarang($request));

        return redirect('/barang');
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        $barang->delete();

        return redirect('/barang');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedBarang(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategoris,id'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
        ]);
    }
}
