# SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del) — Tahap 2

SIPUS-Del adalah aplikasi sistem informasi sirkulasi perpustakaan berbasis **Laravel 11.x** untuk Institut Teknologi Del. Pada Minggu 06 (Tahap 2), proyek dikembangkan dengan fokus pada **Konektivitas Basis Data Persisten, Transaksi ACID (DB::transaction & lockForUpdate), Fluent Query Builder (DB::table), serta Manajemen Sesi & Autentikasi Pustakawan**.

---

## 📌 Kredensial Akun Pustakawan Uji
Sesuai instruksi modul praktikum Minggu 06, akun pustakawan telah disediakan melalui seeder:
- **Email**: `pustakawan@del.ac.id`
- **Kata Sandi**: `password123`
- **Nama**: `Pustakawan Del`

---

## 🏗️ Entity Relationship Diagram (ERD)

```
 +--------------------+       1:N       +------------------------------------+
 |     kategoris      | <-------------- |               bukus                |
 +--------------------+                 +------------------------------------+
 | id (PK)            |                 | id (PK)                            |
 | kode_kategori (UQ) |                 | isbn (UQ)                          |
 | nama_kategori      |                 | judul (Index)                      |
 | timestamps         |                 | penulis                            |
 +--------------------+                 | penerbit                           |
                                        | tahun_terbit                       |
                                        | kategori_id (FK -> kategoris.id)   |
                                        | stok (unsignedInteger)             |
                                        | sinopsis (nullable)                |
                                        | timestamps                         |
                                        +------------------------------------+
                                                           | 1:N
                                                           v
                                        +------------------------------------+
                                        |            peminjamans             |
                                        +------------------------------------+
                                        | id (PK)                            |
                                        | kode_pinjam (UQ)                   |
                                        | buku_id (FK -> bukus.id, CASCADE)  |
                                        | nim_peminjam                       |
                                        | nama_peminjam                      |
                                        | tanggal_pinjam                     |
                                        | tanggal_jatuh_tempo                |
                                        | tanggal_kembali (nullable)         |
                                        | status ('dipinjam'/'dikembalikan') |
                                        | timestamps                         |
                                        | Index: [nim, buku_id, status]      |
                                        +------------------------------------+

 +------------------------------------+       +------------------------------------+
 |               users                |       |              sessions              |
 +------------------------------------+       +------------------------------------+
 | id (PK)                            |       | id (PK string)                     |
 | name                               |       | user_id (FK -> users.id, nullable) |
 | email (UQ)                         |       | ip_address                         |
 | password                           |       | user_agent                         |
 | remember_token                     |       | payload                            |
 | timestamps                         |       | last_activity (Index)              |
 +------------------------------------+       +------------------------------------+
```

---

## 🚀 Fitur & Pemenuhan Spesifikasi Modul Minggu 06

### 1. Skema Tabel Transaksi Sirkulasi (`peminjamans`) & Relasi Eloquent
- Dibuat migrasi `peminjamans` dengan kolom `id`, `kode_pinjam` (unik), `buku_id` (foreign key `constrained('bukus')->cascadeOnDelete()`), `nim_peminjam`, `nama_peminjam`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali` (nullable), `status` ('dipinjam' / 'dikembalikan'), serta `timestamps`.
- Relasi Eloquent dua arah:
  - `Buku` hasMany `Peminjaman` (`$buku->peminjamans()`).
  - `Peminjaman` belongsTo `Buku` (`$peminjaman->buku()`).

### 2. Transaksi Basis Data ACID & Pessimistic Locking (`lockForUpdate`)
- **Peminjaman Buku Atomik**:
  - Dibungkus dalam `DB::transaction(function () use (...) { ... }, 3)`.
  - Mengunci baris buku menggunakan `Buku::where('id', $id)->lockForUpdate()->firstOrFail()` untuk mencegah *Race Condition* pada saat stok kritis.
  - Memvalidasi stok `stok > 0`. Jika stok = 0, memicu pembatalan (`RuntimeException`) sehingga rollback otomatis dan stok tidak menjadi negatif (-1).
  - Mencegah NIM yang sama meminjam buku yang sama jika transaksi peminjaman sebelumnya belum berstatus `'dikembalikan'`.
  - Mengurangi stok secara atomik (`$buku->decrement('stok')`) dan mencatat data ke tabel `peminjamans`.
- **Pengembalian Buku Atomik**:
  - Dibungkus `DB::transaction()`.
  - Mengunci baris peminjaman dan buku terkait via `lockForUpdate()`.
  - Memperbarui status peminjaman menjadi `'dikembalikan'` dan mencatat `tanggal_kembali = now()->toDateString()`.
  - Menambah kembali stok buku secara atomik (`$buku->increment('stok')`).
  - Menangkap `QueryException` dan mencatatnya ke `Log::error()`.

### 3. Fluent Query Builder (`DB::table`), Agregasi `withCount`, & Filter Dinamis
- **Dashboard Kartu Statistik**: Menghitung metrik perpustakaan secara cepat dan efisien menggunakan Fluent Query Builder:
  - Total Judul Buku: `DB::table('bukus')->count()`
  - Total Stok Tersedia: `DB::table('bukus')->sum('stok')`
  - Total Buku Sedang Dipinjam: `DB::table('peminjamans')->where('status', 'dipinjam')->count()`
  - Total Pengembalian: `DB::table('peminjamans')->where('status', 'dikembalikan')->count()`
- **Agregasi Bebas N+1 Problem**: Katalog memuat data relasi kategori sekaligus menghitung frekuensi peminjaman dengan `Buku::with('kategori')->withCount('peminjamans')`.
- **Pencarian Dinamis**: Filter kondisional `when()` untuk kata kunci (judul, penulis, ISBN) dan kategori, dengan preservasi query string `withQueryString()`.

### 4. Manajemen Sesi Pengguna & Autentikasi Pustakawan
- **Login Pustakawan**: Menggunakan `Auth::attempt()`.
- **Mitigasi Session Fixation**: Wajib memanggil `$request->session()->regenerate()` segera setelah login berhasil.
- **Penyimpanan State Sesi**: Menyimpan timestamp login pengguna (`login_timestamp`) dan NIM peminjam terakhir di Server Session.
- **Manajemen Cookie**: Mengantrekan HTTP Cookie aman (`del_last_librarian_email`) menggunakan `Cookie::queue()` dengan atribut bawaan Laravel `HttpOnly`, `SameSite=Lax`, dan terenkripsi.
- **Logout Aman**: Memanggil `Auth::logout()`, memusnahkan sesi (`$request->session()->invalidate()`), serta memperbarui token CSRF (`$request->session()->regenerateToken()`).
- **Proteksi Rute**: Seluruh rute mutasi data (tambah/edit/hapus buku, kategori, serta sirkulasi peminjaman & pengembalian) dilindungi oleh middleware `auth`.

### 5. Seeder & Bukti Uji Otomatis
- `UserSeeder`: Akun pustakawan `pustakawan@del.ac.id`.
- `BukuSeeder`: Dilengkapi buku sampel dengan `stok = 0` (ISBN: `9786020300001`) dan `stok = 1` (ISBN: `9786020300002`).
- `PeminjamanSeeder`: Sampel sirkulasi status `'dipinjam'` dan `'dikembalikan'`.
- Seluruh 10 skenario pengujian di `tests/Feature/PeminjamanAcidTest.php` dan `tests/Feature/AuthSessionTest.php` lulus 100% (42 assertions).

---

## 🧪 Bukti Pengujian Otomatis (Test Suite)

Jalankan perintah pengujian:
```bash
php artisan test
```

Hasil verifikasi:
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\AuthSessionTest
  ✓ login sukses meregenerasi sesi dan memasang cookie
  ✓ login gagal jika kredensial salah
  ✓ logout menghapus sesi pengguna
  ✓ rute mutasi diproteksi middleware auth

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

   PASS  Tests\Feature\PeminjamanAcidTest
  ✓ peminjaman buku berhasil dan mengurangi stok secara atomik
  ✓ peminjaman buku stok nol ditolak dan dirollback
  ✓ nim sama tidak bisa meminjam buku yang sama sebelum dikembalikan
  ✓ pengembalian buku memulihkan stok dan mengubah status

Tests:    10 passed (42 assertions)
```

---

## 💻 Panduan Menjalankan Aplikasi di Lingkungan Lokal

1. **Clone Repositori & Masuk ke Direktori**:
   ```bash
   git clone https://github.com/RotuaTampubolon/ppw-2026-week5-12S24028.git
   cd ppw-2026-week5-12S24028
   git checkout week6-db-session
   ```

2. **Instalasi Dependensi PHP & Frontend**:
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Lingkungan**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Migrasi Basis Data & Eksekusi Seeder**:
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Jalankan Server**:
   ```bash
   php artisan serve
   ```
   Akses antarmuka web melalui: `http://localhost:8000`

---
*Praktikum Pemrograman & Pengujian Web (12S3101) — Institut Teknologi Del*
