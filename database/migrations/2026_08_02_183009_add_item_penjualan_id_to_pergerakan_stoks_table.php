<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pergerakan_stoks', function (Blueprint $table) {
            // Pergerakan Stok adalah riwayat: menghapus Penjualan tidak boleh
            // diam-diam menulis ulang sejarah Stok.
            $table->foreignId('item_penjualan_id')
                ->nullable()
                ->after('barang_id')
                ->constrained('item_penjualans')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pergerakan_stoks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('item_penjualan_id');
        });
    }
};
