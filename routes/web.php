<?php

use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)
|--------------------------------------------------------------------------
|
| Mendaftarkan Route::resource untuk buku dan kategori.
| Laravel secara otomatis membuat 7 aksi RESTful (index, create, store,
| show, edit, update, destroy) dan menerapkan Implicit Route Model Binding.
|
*/

// Redirect root ke daftar buku
Route::redirect('/', '/bukus');

// Resource Routes — 7 aksi CRUD dengan Implicit Route Model Binding
Route::resource('bukus', BukuController::class);
Route::resource('kategoris', KategoriController::class);
