<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)
|--------------------------------------------------------------------------
|
| Minggu 06: Autentikasi Pustakawan, Manajemen Sesi & Cookie, serta
| Transaksi Sirkulasi Peminjaman Berbasis ACID (lockForUpdate).
|
*/

// Redirect root ke katalog buku
Route::redirect('/', '/bukus');

// Autentikasi Pustakawan (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

// Logout Pustakawan (Auth Only)
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rute Terproteksi Auth: Aksi Create/Store/Edit/Update/Delete & Sirkulasi
// Didefinisikan SEBELUM rute parameter wildcard {buku} dan {kategori}
Route::middleware('auth')->group(function () {
    // Sirkulasi Peminjaman & Pengembalian Buku (ACID Transaction)
    Route::get('peminjamans/create', [PeminjamanController::class, 'create'])->name('peminjamans.create');
    Route::post('peminjamans', [PeminjamanController::class, 'store'])->name('peminjamans.store');
    Route::patch('peminjamans/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjamans.kembali');

    // Mutasi Data Buku (Create ditaruh sebelum {buku})
    Route::get('bukus/create', [BukuController::class, 'create'])->name('bukus.create');
    Route::post('bukus', [BukuController::class, 'store'])->name('bukus.store');
    Route::get('bukus/{buku}/edit', [BukuController::class, 'edit'])->name('bukus.edit');
    Route::put('bukus/{buku}', [BukuController::class, 'update'])->name('bukus.update');
    Route::delete('bukus/{buku}', [BukuController::class, 'destroy'])->name('bukus.destroy');

    // Mutasi Data Kategori (Create ditaruh sebelum {kategori})
    Route::get('kategoris/create', [KategoriController::class, 'create'])->name('kategoris.create');
    Route::post('kategoris', [KategoriController::class, 'store'])->name('kategoris.store');
    Route::get('kategoris/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategoris.edit');
    Route::put('kategoris/{kategori}', [KategoriController::class, 'update'])->name('kategoris.update');
    Route::delete('kategoris/{kategori}', [KategoriController::class, 'destroy'])->name('kategoris.destroy');
});

// Rute Publik (Katalog & Informasi)
Route::get('bukus', [BukuController::class, 'index'])->name('bukus.index');
Route::get('bukus/{buku}', [BukuController::class, 'show'])->name('bukus.show');

Route::get('kategoris', [KategoriController::class, 'index'])->name('kategoris.index');
Route::get('kategoris/{kategori}', [KategoriController::class, 'show'])->name('kategoris.show');

Route::get('peminjamans', [PeminjamanController::class, 'index'])->name('peminjamans.index');
