<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat sampel transaksi peminjaman (dipinjam & dikembalikan) untuk pengujian.
     */
    public function run(): void
    {
        $buku1 = Buku::where('isbn', '9780062315007')->first(); // To Kill a Mockingbird
        $buku2 = Buku::where('isbn', '9780141439518')->first(); // Pride and Prejudice
        $buku3 = Buku::where('isbn', '9780743273565')->first(); // The Great Gatsby

        if (!$buku1 || !$buku2 || !$buku3) {
            return;
        }

        $transaksis = [
            [
                'kode_pinjam'         => 'PINJ-20261010-001',
                'buku_id'             => $buku1->id,
                'nim_peminjam'        => '12S24028',
                'nama_peminjam'       => 'Rotua Immanuela Tampubolon',
                'tanggal_pinjam'      => now()->subDays(5)->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(2)->toDateString(),
                'tanggal_kembali'     => null,
                'status'              => 'dipinjam',
            ],
            [
                'kode_pinjam'         => 'PINJ-20261011-002',
                'buku_id'             => $buku2->id,
                'nim_peminjam'        => '12S24050',
                'nama_peminjam'       => 'Budi Santoso',
                'tanggal_pinjam'      => now()->subDays(3)->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                'tanggal_kembali'     => null,
                'status'              => 'dipinjam',
            ],
            [
                'kode_pinjam'         => 'PINJ-20261001-003',
                'buku_id'             => $buku3->id,
                'nim_peminjam'        => '12S24010',
                'nama_peminjam'       => 'Siti Rahma',
                'tanggal_pinjam'      => now()->subDays(15)->toDateString(),
                'tanggal_jatuh_tempo' => now()->subDays(8)->toDateString(),
                'tanggal_kembali'     => now()->subDays(7)->toDateString(),
                'status'              => 'dikembalikan',
            ],
            [
                'kode_pinjam'         => 'PINJ-20261002-004',
                'buku_id'             => $buku1->id,
                'nim_peminjam'        => '12S24099',
                'nama_peminjam'       => 'Andi Wijaya',
                'tanggal_pinjam'      => now()->subDays(20)->toDateString(),
                'tanggal_jatuh_tempo' => now()->subDays(13)->toDateString(),
                'tanggal_kembali'     => now()->subDays(12)->toDateString(),
                'status'              => 'dikembalikan',
            ],
        ];

        foreach ($transaksis as $t) {
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => $t['kode_pinjam']],
                $t
            );
        }

        $this->command->info('✅ PeminjamanSeeder: 4 data transaksi sirkulasi berhasil dibuat.');
    }
}
