@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <ul>
        @foreach ($kategoris as $kategori)
            <li>
                <form method="POST" action="/kategori/{{ $kategori->id }}">
                    @csrf
                    @method('PUT')
                    <input type="text" name="nama" value="{{ $kategori->nama }}" required>
                    <button type="submit">Simpan</button>
                </form>

                <form method="POST" action="/kategori/{{ $kategori->id }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            </li>
        @endforeach
    </ul>

    <h2>Tambah Kategori</h2>
    <form method="POST" action="/kategori">
        @csrf
        <label for="nama">Nama</label>
        <input id="nama" type="text" name="nama" value="{{ old('nama') }}" required>

        <button type="submit">Tambah</button>
    </form>
@endsection
