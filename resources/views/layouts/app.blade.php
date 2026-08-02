<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') - POS Skripsi</title>
</head>
<body>
    @auth
        <nav>
            <a href="/penjualan">Penjualan</a>
            <a href="/barang">Barang</a>
            @if (auth()->user()->isOwner())
                <a href="/kategori">Kategori</a>
                <a href="/pengeluaran">Pengeluaran</a>
                <a href="/kasir">Kasir</a>
            @endif

            <form method="POST" action="/logout">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </nav>
    @endauth

    <h1>@yield('title')</h1>

    @include('partials.errors')

    @yield('content')
</body>
</html>
