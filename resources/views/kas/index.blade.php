@extends('layouts.app')

@section('title', 'Kas & Laporan')

@section('content')
    <form method="GET" action="/kas">
        <label for="tanggal_awal">Tanggal Awal</label>
        <input id="tanggal_awal" type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" required>

        <label for="tanggal_akhir">Tanggal Akhir</label>
        <input id="tanggal_akhir" type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" required>

        <button type="submit">Tampilkan</button>
    </form>

    @if ($ringkasan)
        <h2>Ringkasan Kas {{ $ringkasan->tanggalAwal }} s/d {{ $ringkasan->tanggalAkhir }}</h2>
        <ul>
            <li>Total Penjualan: <span data-total-penjualan="{{ $ringkasan->totalPenjualan() }}">{{ $ringkasan->totalPenjualan() }}</span></li>
            <li>Total Pengeluaran: <span data-total-pengeluaran="{{ $ringkasan->totalPengeluaran() }}">{{ $ringkasan->totalPengeluaran() }}</span></li>
            <li>Kas: <span data-kas="{{ $ringkasan->kas() }}">{{ $ringkasan->kas() }}</span></li>
        </ul>

        @php
            $rentang = ['tanggal_awal' => $ringkasan->tanggalAwal, 'tanggal_akhir' => $ringkasan->tanggalAkhir];
        @endphp

        <h2>Laporan Penjualan</h2>
        <p><a href="/laporan/penjualan/pdf?{{ http_build_query($rentang) }}">Export PDF</a></p>
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
                        <td data-total="{{ $penjualan->total }}">{{ $penjualan->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Laporan Pengeluaran</h2>
        <p><a href="/laporan/pengeluaran/pdf?{{ http_build_query($rentang) }}">Export PDF</a></p>
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
                        <td data-jumlah="{{ $pengeluaran->jumlah }}">{{ $pengeluaran->jumlah }}</td>
                        <td>{{ $pengeluaran->keterangan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Pilih rentang tanggal untuk melihat Kas.</p>
    @endif
@endsection
