<?php

namespace App\Models;

use App\Enums\TipePergerakanStok;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['barang_id', 'item_penjualan_id', 'tipe', 'jumlah', 'tanggal', 'keterangan'])]
class PergerakanStok extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tipe' => TipePergerakanStok::class,
            'jumlah' => 'integer',
            'tanggal' => 'date',
        ];
    }
}
