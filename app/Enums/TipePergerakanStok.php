<?php

namespace App\Enums;

enum TipePergerakanStok: string
{
    case Masuk = 'masuk';
    case Keluar = 'keluar';

    public function label(): string
    {
        return match ($this) {
            self::Masuk => 'Stok Masuk',
            self::Keluar => 'Stok Keluar',
        };
    }
}
