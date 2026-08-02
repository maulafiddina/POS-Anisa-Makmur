<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <p>Rentang: {{ $ringkasan->tanggalAwal }} s/d {{ $ringkasan->tanggalAkhir }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ringkasan->penjualans as $penjualan)
                <tr>
                    <td>{{ $penjualan->tanggal->toDateString() }}</td>
                    <td>{{ $penjualan->kasir->name }}</td>
                    <td>{{ $penjualan->total }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Penjualan</td>
                <td>{{ $ringkasan->totalPenjualan() }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
