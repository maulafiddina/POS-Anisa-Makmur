@extends('layouts.app')

@section('title', 'Akun Kasir')

@section('content')
    <ul>
        @foreach ($kasirs as $kasir)
            <li>{{ $kasir->name }} ({{ $kasir->email }})</li>
        @endforeach
    </ul>

    <h2>Tambah Akun Kasir</h2>
    <form method="POST" action="/kasir">
        @csrf
        <label for="name">Nama</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Buat Akun Kasir</button>
    </form>
@endsection
