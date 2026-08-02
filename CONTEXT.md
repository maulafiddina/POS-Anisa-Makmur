# POS Skripsi

Aplikasi web Point of Sale (POS) berbasis Laravel untuk satu outlet toko, mencakup pencatatan penjualan, pengeluaran, stok barang, dan export dokumen/laporan.

## Language

### Pengguna

**Owner**:
Pengguna dengan akses penuh ke seluruh sistem: Barang, Kategori, Stok (termasuk Stok Masuk dan Stok Minimum), Pengeluaran, Penjualan, dan Laporan.
_Avoid_: Admin (dipakai sebagai sinonim Owner, bukan role terpisah), User

**Kasir**:
Pengguna dengan akses terbatas: bisa membuat dan melihat Penjualan, serta melihat (read-only) Stok Barang. Tidak bisa mengelola Barang, Kategori, Pengeluaran, Stok Masuk, atau Laporan.
_Avoid_: Cashier, Employee

### Penjualan & Kas

**Penjualan**:
Transaksi ketika Owner menjual barang ke pembeli. Satu Penjualan berisi satu atau lebih Item Penjualan (seperti keranjang belanja). Setiap Penjualan menghasilkan catatan uang masuk (lihat Kas) dan mengurangi Stok Barang terkait.
_Avoid_: Transaksi, Order

**Item Penjualan**:
Satu baris dalam Penjualan yang mencatat satu Barang, jumlah, dan subtotalnya.
_Avoid_: Cart item, Detail penjualan

**Pengeluaran**:
Catatan uang keluar yang dicatat terpisah dari Penjualan (misal biaya operasional, belanja bahan, dll). Dicatat flat (tanggal, jumlah, keterangan) tanpa kategori.
_Avoid_: Expense, Biaya

**Kas**:
Ringkasan uang yang dihitung dari total Penjualan dikurangi total Pengeluaran. Bukan buku besar/ledger terpisah — nilainya diturunkan (derived) dari catatan Penjualan dan Pengeluaran, bukan dicatat manual sebagai entri kas independen.
_Avoid_: Buku kas, Cash ledger, Neraca

### Barang & Stok

**Barang**:
Satu jenis produk yang dijual di toko. Memiliki Kategori, Harga Jual, dan Stok Minimum. Dihitung per pcs (tidak ada satuan lain). Tidak mencatat Harga Beli — Kas berbasis omzet, bukan laba/margin.
_Avoid_: Produk, Item

**Kategori**:
Pengelompokan Barang (misal: Makanan, Minuman, Alat Tulis) untuk navigasi dan pelaporan.
_Avoid_: Jenis, Tipe

**Pergerakan Stok**:
Riwayat/log setiap perubahan Stok suatu Barang, baik bertambah (Stok Masuk) maupun berkurang (karena Penjualan). Stok Barang saat ini adalah nilai yang diturunkan dari total Pergerakan Stok, bukan angka yang diedit langsung.
_Avoid_: Stock log, Kartu stok

**Stok Masuk**:
Pergerakan Stok yang menambah jumlah Barang (restock), dicatat dengan jumlah, tanggal, dan keterangan opsional. Tidak melacak Supplier.
_Avoid_: Restock, Purchase order

**Stok Minimum**:
Ambang batas per Barang; ketika Stok Barang saat ini berada di bawah atau sama dengan Stok Minimum, sistem menandai Barang tersebut sebagai stok menipis.
_Avoid_: Reorder point, Safety stock

### Laporan

**Laporan Penjualan**:
Ringkasan Penjualan pada rentang tanggal tertentu (custom range), dapat di-export sebagai PDF.
_Avoid_: Sales report

**Laporan Pengeluaran**:
Ringkasan Pengeluaran pada rentang tanggal tertentu (custom range), dapat di-export sebagai PDF.
_Avoid_: Expense report
