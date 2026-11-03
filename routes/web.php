<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)
|--------------------------------------------------------------------------
*/

// Redirect root ke katalog buku
Route::redirect('/', '/bukus');

// Resource Routes Buku & Kategori
Route::resource('bukus', BukuController::class);
Route::resource('kategoris', KategoriController::class);

// Sirkulasi Peminjaman & Pengembalian Buku (ACID Transaction & Pessimistic Locking)
Route::get('peminjamans', [PeminjamanController::class, 'index'])->name('peminjamans.index');
Route::get('peminjamans/create', [PeminjamanController::class, 'create'])->name('peminjamans.create');
Route::post('peminjamans', [PeminjamanController::class, 'store'])->name('peminjamans.store');
Route::patch('peminjamans/{id}/kembali', [PeminjamanController::class, 'kembali'])->name('peminjamans.kembali');
