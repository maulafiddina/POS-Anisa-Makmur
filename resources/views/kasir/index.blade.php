<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kasir - POS Skripsi</title>
</head>
<body>
    <h1>Akun Kasir</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <ul>
        @foreach ($kasirs as $kasir)
            <li>{{ $kasir->name }} ({{ $kasir->email }})</li>
        @endforeach
    </ul>

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
</body>
</html>
