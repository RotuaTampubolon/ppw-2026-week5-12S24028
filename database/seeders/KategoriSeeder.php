<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            ['kode_kategori' => 'KAT-FIK', 'nama_kategori' => 'Fiksi'],
            ['kode_kategori' => 'KAT-NON', 'nama_kategori' => 'Non-Fiksi'],
            ['kode_kategori' => 'KAT-SAI', 'nama_kategori' => 'Sains & Teknologi'],
            ['kode_kategori' => 'KAT-SEJ', 'nama_kategori' => 'Sejarah'],
            ['kode_kategori' => 'KAT-SAS', 'nama_kategori' => 'Sastra & Budaya'],
            ['kode_kategori' => 'KAT-AGM', 'nama_kategori' => 'Agama & Spiritualitas'],
            ['kode_kategori' => 'KAT-EKO', 'nama_kategori' => 'Ekonomi & Bisnis'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::firstOrCreate(
                ['kode_kategori' => $kategori['kode_kategori']],
                ['nama_kategori' => $kategori['nama_kategori']]
            );
        }

        $this->command->info('✅ KategoriSeeder: ' . count($kategoris) . ' kategori berhasil dibuat.');
    }
}
