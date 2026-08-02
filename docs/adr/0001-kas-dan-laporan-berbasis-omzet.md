---
status: accepted
---

# Kas dan Laporan berbasis omzet, bukan laba, dan mengasumsikan pembayaran tunai

Kas dihitung sebagai total Penjualan dikurangi total Pengeluaran. Barang tidak mencatat Harga Beli, dan Penjualan tidak mencatat metode pembayaran (diasumsikan seluruhnya tunai). Kami memilih ini agar model data dan implementasi tetap sederhana untuk skop skripsi POS satu outlet, dengan trade-off bahwa Laporan Penjualan/Pengeluaran tidak bisa menunjukkan laba/margin sebenarnya, dan asumsi tunai tidak akan akurat kalau nanti ada pembayaran non-tunai (transfer, QRIS, dll).

## Consequences

Jika ke depan dibutuhkan laporan laba/margin atau dukungan pembayaran non-tunai, Barang perlu ditambah field Harga Beli dan Penjualan perlu ditambah field metode pembayaran — data historis yang sudah ada tidak akan memiliki nilai untuk field-field baru tersebut.
