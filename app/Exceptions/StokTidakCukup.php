<?php

namespace App\Exceptions;

use App\Models\Barang;
use RuntimeException;

class StokTidakCukup extends RuntimeException
{
    public function __construct(
        public readonly Barang $barang,
        public readonly int $diminta,
        public readonly int $tersedia,
    ) {
        parent::__construct(
            "Stok {$barang->nama} tidak cukup: diminta {$diminta}, tersedia {$tersedia}."
        );
    }
}
