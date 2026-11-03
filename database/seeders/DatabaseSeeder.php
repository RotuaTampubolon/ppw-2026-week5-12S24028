<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Urutan seeder penting: kategori → buku → user → peminjaman
     */
    public function run(): void
    {
        $this->call([
            KategoriSeeder::class,
            BukuSeeder::class,
            BukuTestSeeder::class,
            UserSeeder::class,
            PeminjamanSeeder::class,
        ]);
    }
}
