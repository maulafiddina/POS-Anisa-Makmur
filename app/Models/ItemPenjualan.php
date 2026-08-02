<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['penjualan_id', 'barang_id', 'jumlah', 'harga_jual', 'subtotal'])]
class ItemPenjualan extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_jual' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }
}
