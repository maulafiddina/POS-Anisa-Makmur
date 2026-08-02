@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <form method="POST" action="/login">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
@endsection
