<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KasirController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'owner'])->group(function () {
    Route::get('/kasir', [KasirController::class, 'index']);
    Route::post('/kasir', [KasirController::class, 'store']);
});
