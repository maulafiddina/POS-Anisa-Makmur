@extends('layouts.app')

@section('title', 'Pergerakan Stok: '.$barang->nama)

@section('content')
    <p>Stok saat ini: {{ $barang->stok() }}</p>

    <h2>Catat Stok Masuk</h2>
    <form method="POST" action="/barang/{{ $barang->id }}/stok-masuk">
        @csrf
        <label for="jumlah">Jumlah</label>
        <input id="jumlah" type="number" name="jumlah" value="{{ old('jumlah') }}" min="1" required>

        <label for="tanggal">Tanggal</label>
        <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required>

        <label for="keterangan">Keterangan</label>
        <input id="keterangan" type="text" name="keterangan" value="{{ old('keterangan') }}">

        <button type="submit">Catat</button>
    </form>

    <h2>Riwayat</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pergerakanStoks as $pergerakanStok)
                <tr>
                    <td>{{ $pergerakanStok->tanggal->toDateString() }}</td>
                    <td>{{ $pergerakanStok->tipe->label() }}</td>
                    <td>{{ $pergerakanStok->jumlah }}</td>
                    <td>{{ $pergerakanStok->keterangan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
