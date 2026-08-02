<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('kategori.index', [
            'kategoris' => Kategori::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Kategori::create($this->validatedKategori($request));

        return redirect('/kategori');
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($this->validatedKategori($request, $kategori));

        return redirect('/kategori');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->barangs()->exists()) {
            return redirect('/kategori')->withErrors([
                'kategori' => 'Kategori masih dipakai oleh Barang dan tidak bisa dihapus.',
            ]);
        }

        $kategori->delete();

        return redirect('/kategori');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedKategori(Request $request, ?Kategori $kategori = null): array
    {
        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategoris', 'nama')->ignore($kategori),
            ],
        ]);
    }
}
