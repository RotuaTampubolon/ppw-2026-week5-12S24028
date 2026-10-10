# SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)

Proyek ini adalah implementasi sistem perpustakaan menggunakan arsitektur Model-View-Controller (MVC) server-side pada Laravel 11.x.

## Fitur & Implementasi Berdasarkan Spesifikasi
- **Arsitektur MVC & Routing RESTful**: Memanfaatkan resource controller lengkap (7 method) dan implicit route model binding di `routes/web.php` untuk `BukuController` dan `KategoriController`.
- **Model Relasional & Eloquent ORM**: Menggunakan relasi `HasMany` (Kategori) dan `BelongsTo` (Buku) serta proteksi mass assignment via `$fillable`. Pencegahan N+1 Query Problem diimplementasikan menggunakan Eager Loading `with('kategori')`.
- **Validasi Input & Keamanan Web**: Form divalidasi ketat di sisi server (ISBN unique, judul minimal 5 karakter, stok minimal 0). Seluruh form dilindungi CSRF token `@csrf` dan pencegahan XSS bawaan dari Blade `{{ }}`.
- **Blade Templating & Desain UI**: Membangun antar muka dengan master shell layout komponen Blade `<x-layout>`, tema Del Purple, flash message handling interaktif, @forelse untuk kondisi data kosong, dan TailwindCSS via Vite.
- **Data Uji Realistis**: Dilengkapi dengan Factory Faker (menghasilkan ISBN valid dan data buku fiktif realistis) dan Seeder (DatabaseSeeder memanggil KategoriSeeder dan BukuSeeder) sesuai requirement.

## Audit SQL Eager Loading
Log query untuk membuktikan N+1 Problem dicegah tersedia pada berkas `eager-loading-audit.log`. Data dipanggil menggunakan `Buku::with('kategori')->paginate(10);`

## Prasyarat
- PHP 8.2 atau yang lebih baru dengan ekstensi PDO, SQLite, mbstring.
- Composer 2.x
- Node.js & NPM (untuk Vite bundling)

## Panduan Instalasi
1. Clone repositori ini
2. Salin `.env.example` ke `.env`
3. Jalankan `composer install`
4. Jalankan `npm install` dan `npm run build`
5. Generate application key: `php artisan key:generate`
6. Migrasi database dan sediakan mock data: `php artisan migrate:fresh --seed`
7. Jalankan server lokal: `php artisan serve`

*Dikembangkan untuk Praktikum Pemrograman & Pengujian Web 2026/2027*
