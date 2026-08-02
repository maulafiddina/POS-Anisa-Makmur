<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran</title>
</head>
<body>
    <h1>Laporan Pengeluaran</h1>
    <p>Rentang: {{ $ringkasan->tanggalAwal }} s/d {{ $ringkasan->tanggalAkhir }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ringkasan->pengeluarans as $pengeluaran)
                <tr>
                    <td>{{ $pengeluaran->tanggal->toDateString() }}</td>
                    <td>{{ $pengeluaran->jumlah }}</td>
                    <td>{{ $pengeluaran->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Pengeluaran</td>
                <td>{{ $ringkasan->totalPengeluaran() }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
