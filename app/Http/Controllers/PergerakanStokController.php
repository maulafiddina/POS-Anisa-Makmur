<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\View\View;

class PergerakanStokController extends Controller
{
    public function index(Barang $barang): View
    {
        return view('pergerakan-stok.index', [
            'barang' => $barang,
            'pergerakanStoks' => $barang->pergerakanStoks()
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get(),
        ]);
    }
}
