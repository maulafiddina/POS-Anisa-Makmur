<?php

namespace App\Http\Controllers;

use App\Services\RingkasanKas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KasController extends Controller
{
    public function index(Request $request): View
    {
        $rentang = $request->validate([
            'tanggal_awal' => ['nullable', 'date', 'required_with:tanggal_akhir'],
            'tanggal_akhir' => ['nullable', 'date', 'required_with:tanggal_awal', 'after_or_equal:tanggal_awal'],
        ]);

        return view('kas.index', [
            'ringkasan' => filled($rentang['tanggal_awal'] ?? null)
                ? RingkasanKas::untukRentang($rentang['tanggal_awal'], $rentang['tanggal_akhir'])
                : null,
        ]);
    }
}
