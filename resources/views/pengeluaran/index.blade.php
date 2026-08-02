@extends('layouts.app')

@section('title', 'Pengeluaran')

@section('content')
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengeluarans as $pengeluaran)
                <tr>
                    <td>{{ $pengeluaran->tanggal->toDateString() }}</td>
                    <td data-jumlah="{{ $pengeluaran->jumlah }}">{{ $pengeluaran->jumlah }}</td>
                    <td>{{ $pengeluaran->keterangan }}</td>
                    <td>
                        <form method="POST" action="/pengeluaran/{{ $pengeluaran->id }}">
                            @csrf
                            @method('PUT')
                            <input type="date" name="tanggal" value="{{ $pengeluaran->tanggal->toDateString() }}" required>
                            <input type="number" name="jumlah" value="{{ $pengeluaran->jumlah }}" min="1" required>
                            <input type="text" name="keterangan" value="{{ $pengeluaran->keterangan }}" required>
                            <button type="submit">Simpan</button>
                        </form>

                        @include('partials.hapus-form', ['action' => "/pengeluaran/{$pengeluaran->id}"])
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Tambah Pengeluaran</h2>
    <form method="POST" action="/pengeluaran">
        @csrf
        <label for="tanggal">Tanggal</label>
        <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required>

        <label for="jumlah">Jumlah</label>
        <input id="jumlah" type="number" name="jumlah" value="{{ old('jumlah') }}" min="1" required>

        <label for="keterangan">Keterangan</label>
        <input id="keterangan" type="text" name="keterangan" value="{{ old('keterangan') }}" required>

        <button type="submit">Tambah</button>
    </form>
@endsection
