<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeminjamanSeeder extends Seeder
{
    /**
     * Seed data peminjaman contoh.
     * Menggunakan DB::transaction() sesuai dengan best practice ACID.
     */
    public function run(): void
    {
        // Ambil buku untuk sample data
        $buku1 = Buku::where('isbn', '9780062315007')->first(); // To Kill a Mockingbird - stok 15
        $buku2 = Buku::where('isbn', '9780141439518')->first(); // Pride and Prejudice - stok 12
        $buku3 = Buku::where('isbn', '9780132350884')->first(); // Clean Code - stok 25

        if (!$buku1 || !$buku2 || !$buku3) {
            $this->command->warn('⚠️  PeminjamanSeeder: Buku referensi tidak ditemukan. Jalankan BukuSeeder terlebih dahulu.');
            return;
        }

        $peminjamans = [
            // Peminjaman aktif (status = dipinjam)
            [
                'kode_pinjam'         => 'PJM-SEED0001',
                'buku_id'             => $buku1->id,
                'nim_peminjam'        => '12S24001',
                'nama_peminjam'       => 'Budi Santoso',
                'tanggal_pinjam'      => now()->subDays(3)->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                'tanggal_kembali'     => null,
                'status'              => 'dipinjam',
            ],
            [
                'kode_pinjam'         => 'PJM-SEED0002',
                'buku_id'             => $buku2->id,
                'nim_peminjam'        => '12S24002',
                'nama_peminjam'       => 'Siti Nurhaliza',
                'tanggal_pinjam'      => now()->subDays(10)->toDateString(),
                'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
                'tanggal_kembali'     => null,
                'status'              => 'dipinjam',
            ],
            // Peminjaman selesai (status = dikembalikan)
            [
                'kode_pinjam'         => 'PJM-SEED0003',
                'buku_id'             => $buku3->id,
                'nim_peminjam'        => '12S24003',
                'nama_peminjam'       => 'Ahmad Fauzi',
                'tanggal_pinjam'      => now()->subDays(20)->toDateString(),
                'tanggal_jatuh_tempo' => now()->subDays(13)->toDateString(),
                'tanggal_kembali'     => now()->subDays(15)->toDateString(),
                'status'              => 'dikembalikan',
            ],
        ];

        $count = 0;
        foreach ($peminjamans as $data) {
            if (!Peminjaman::where('kode_pinjam', $data['kode_pinjam'])->exists()) {
                DB::transaction(function () use ($data, $buku1, $buku2, $buku3, &$count) {
                    Peminjaman::create($data);
                    // Kurangi stok untuk yang masih dipinjam
                    if ($data['status'] === 'dipinjam') {
                        Buku::where('id', $data['buku_id'])->decrement('stok');
                    }
                    $count++;
                });
            }
        }

        $this->command->info("✅ PeminjamanSeeder: {$count} data peminjaman berhasil dibuat.");
    }
}
