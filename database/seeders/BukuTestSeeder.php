<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuTestSeeder extends Seeder
{
    /**
     * Tambahkan 2 buku khusus untuk pengujian ACID Transaction:
     *  - 1 buku dengan stok = 0  → uji penolakan peminjaman saat stok habis
     *  - 1 buku dengan stok = 1  → uji pengurangan stok atomik
     */
    public function run(): void
    {
        // Pastikan kategori Sains & Teknologi ada
        $kategori = Kategori::where('kode_kategori', 'KAT-SAI')->first();
        if (!$kategori) {
            $kategori = Kategori::first();
        }

        // Buku dengan stok = 0 (untuk menguji penolakan stok habis)
        Buku::firstOrCreate(
            ['isbn' => '9780000000001'],
            [
                'judul'        => '[TEST] Buku Stok Habis',
                'penulis'      => 'Test Author',
                'penerbit'     => 'Test Publisher',
                'tahun_terbit' => 2024,
                'kategori_id'  => $kategori->id,
                'stok'         => 0,
                'sinopsis'     => 'Buku dummy untuk menguji penolakan ACID saat stok = 0.',
            ]
        );

        // Buku dengan stok = 1 (untuk menguji pengurangan atomik)
        Buku::firstOrCreate(
            ['isbn' => '9780000000002'],
            [
                'judul'        => '[TEST] Buku Stok Satu',
                'penulis'      => 'Test Author',
                'penerbit'     => 'Test Publisher',
                'tahun_terbit' => 2024,
                'kategori_id'  => $kategori->id,
                'stok'         => 1,
                'sinopsis'     => 'Buku dummy untuk menguji pengurangan stok atomik saat stok = 1.',
            ]
        );

        $this->command->info('✅ BukuTestSeeder: 2 buku ACID test berhasil dibuat.');
    }
}
