<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\Soap\LibrarySoapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)
|--------------------------------------------------------------------------
|
| Week 6: Menambahkan route autentikasi, peminjaman (ACID transaction),
| serta proteksi middleware auth untuk seluruh operasi mutasi data.
|
*/

// Redirect root ke daftar buku
Route::redirect('/', '/bukus');

// ── Autentikasi Pustakawan ─────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── Buku (CRUD) — Read publik, Mutasi hanya untuk auth ────────────────────
Route::get('/bukus', [BukuController::class, 'index'])->name('bukus.index');

// PENTING: Route literal HARUS didefinisikan SEBELUM route wildcard {buku}
// agar /bukus/create tidak ditangkap sebagai model binding parameter
Route::middleware('auth')->group(function () {
    Route::get('/bukus/create', [BukuController::class, 'create'])->name('bukus.create');
    Route::post('/bukus', [BukuController::class, 'store'])->name('bukus.store');
});

Route::get('/bukus/{buku}', [BukuController::class, 'show'])->name('bukus.show');

Route::middleware('auth')->group(function () {
    Route::get('/bukus/{buku}/edit', [BukuController::class, 'edit'])->name('bukus.edit');
    Route::put('/bukus/{buku}', [BukuController::class, 'update'])->name('bukus.update');
    Route::delete('/bukus/{buku}', [BukuController::class, 'destroy'])->name('bukus.destroy');
});

// ── Kategori (CRUD) — Seluruhnya hanya untuk auth ─────────────────────────
Route::middleware('auth')->group(function () {
    Route::resource('kategoris', KategoriController::class);
});

// ── Peminjaman — Seluruhnya hanya untuk auth ──────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/peminjamans', [PeminjamanController::class, 'index'])->name('peminjamans.index');
    Route::get('/peminjamans/buku/{buku}/create', [PeminjamanController::class, 'create'])->name('peminjamans.create');
    Route::post('/peminjamans/buku/{buku}', [PeminjamanController::class, 'store'])->name('peminjamans.store');
    Route::patch('/peminjamans/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjamans.kembalikan');
});

   // Layanan SOAP Bebas Pustaka (Minggu 09)
   Route::get('/soap/sipus.wsdl', [LibrarySoapController::class, 'wsdl']);
   Route::post('/soap/sipus', [LibrarySoapController::class, 'handle']);