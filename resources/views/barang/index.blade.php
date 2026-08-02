@extends('layouts.app')

@section('title', 'Barang')

@section('content')
    @php($isOwner = auth()->user()->isOwner())

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga Jual</th>
                <th>Stok</th>
                <th>Stok Minimum</th>
                @if ($isOwner)
                    <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($barangs as $barang)
                <tr>
                    <td>{{ $barang->nama }}</td>
                    <td>{{ $barang->kategori->nama }}</td>
                    <td>{{ $barang->harga_jual }}</td>
                    <td data-stok="{{ $barang->stok() }}">
                        {{ $barang->stok() }}
                        @if ($barang->stokMenipis())
                            <strong>Stok menipis</strong>
                        @endif
                    </td>
                    <td>{{ $barang->stok_minimum }}</td>
                    @if ($isOwner)
                        <td>
                            <a href="/barang/{{ $barang->id }}/pergerakan-stok">Pergerakan Stok</a>

                            <form method="POST" action="/barang/{{ $barang->id }}">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nama" value="{{ $barang->nama }}" required>
                                <select name="kategori_id" required>
                                    @foreach ($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" @selected($kategori->id === $barang->kategori_id)>
                                            {{ $kategori->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="number" name="harga_jual" value="{{ $barang->harga_jual }}" min="0" required>
                                <input type="number" name="stok_minimum" value="{{ $barang->stok_minimum }}" min="0" required>
                                <button type="submit">Simpan</button>
                            </form>

                            <form method="POST" action="/barang/{{ $barang->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($isOwner)
        <h2>Tambah Barang</h2>
        <form method="POST" action="/barang">
            @csrf
            <label for="nama">Nama</label>
            <input id="nama" type="text" name="nama" value="{{ old('nama') }}" required>

            <label for="kategori_id">Kategori</label>
            <select id="kategori_id" name="kategori_id" required>
                @foreach ($kategoris as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                @endforeach
            </select>

            <label for="harga_jual">Harga Jual</label>
            <input id="harga_jual" type="number" name="harga_jual" value="{{ old('harga_jual') }}" min="0" required>

            <label for="stok_minimum">Stok Minimum</label>
            <input id="stok_minimum" type="number" name="stok_minimum" value="{{ old('stok_minimum', 0) }}" min="0" required>

            <button type="submit">Tambah</button>
        </form>
    @endif
@endsection
