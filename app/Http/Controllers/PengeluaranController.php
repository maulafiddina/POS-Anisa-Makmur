<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengeluaranController extends Controller
{
    public function index(): View
    {
        return view('pengeluaran.index', [
            'pengeluarans' => Pengeluaran::orderByDesc('tanggal')->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Pengeluaran::create($this->validatedPengeluaran($request));

        return redirect('/pengeluaran');
    }

    public function update(Request $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $pengeluaran->update($this->validatedPengeluaran($request));

        return redirect('/pengeluaran');
    }

    public function destroy(Pengeluaran $pengeluaran): RedirectResponse
    {
        $pengeluaran->delete();

        return redirect('/pengeluaran');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPengeluaran(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'keterangan' => ['required', 'string', 'max:255'],
        ]);
    }
}
