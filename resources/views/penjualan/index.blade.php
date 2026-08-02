@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    <h2>Penjualan Baru</h2>
    <form method="POST" action="/penjualan">
        @csrf
        <label for="tanggal">Tanggal</label>
        <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required>

        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @for ($baris = 0; $baris < 5; $baris++)
                    <tr>
                        <td>
                            <select name="items[{{ $baris }}][barang_id]">
                                <option value="">— pilih barang —</option>
                                @foreach ($barangs as $barang)
                                    <option value="{{ $barang->id }}" @selected(old("items.{$baris}.barang_id") == $barang->id)>{{ $barang->nama }} ({{ $barang->harga_jual }}, stok {{ $barang->stok() }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[{{ $baris }}][jumlah]" min="1" value="{{ old("items.{$baris}.jumlah") }}">
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <button type="submit">Simpan Penjualan</button>
    </form>

    <h2>Riwayat Penjualan</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualans as $penjualan)
                <tr>
                    <td>{{ $penjualan->tanggal->toDateString() }}</td>
                    <td>{{ $penjualan->kasir->name }}</td>
                    <td data-total="{{ $penjualan->total }}">{{ $penjualan->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
