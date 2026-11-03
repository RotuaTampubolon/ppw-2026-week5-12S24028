<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * AcidTransactionTest — Menguji ACID Transaction pada peminjaman dan pengembalian buku.
 *
 * Kriteria Rubrik:
 * - DB::transaction() + lockForUpdate() pada store() dan kembalikan()
 * - Penolakan peminjaman saat stok = 0
 * - Penolakan peminjaman duplikat NIM untuk buku yang sama
 * - Stok berkurang atomik saat pinjam, bertambah saat kembali
 * - Penolakan pengembalian buku yang sudah dikembalikan
 */
class AcidTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $pustakawan;
    private Buku $bukuNormal;
    private Buku $bukuStokNol;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->pustakawan  = User::where('email', 'pustakawan@del.ac.id')->first();
        $this->bukuNormal  = Buku::where('isbn', '9780000000002')->first(); // stok = 1
        $this->bukuStokNol = Buku::where('isbn', '9780000000001')->first(); // stok = 0
    }

    // ────────────────────────────────────────────────────────────
    // 1. PEMINJAMAN BERHASIL (Stok Berkurang Atomik)
    // ────────────────────────────────────────────────────────────

    public function test_peminjaman_berhasil_dan_stok_berkurang(): void
    {
        $stokAwal = $this->bukuNormal->stok; // = 1

        $response = $this->actingAs($this->pustakawan)->post(
            route('peminjamans.store', $this->bukuNormal),
            [
                'nim_peminjam'        => '12S24028',
                'nama_peminjam'       => 'Test Mahasiswa',
                'tanggal_pinjam'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            ]
        );

        $response->assertRedirect(route('peminjamans.index'));
        $response->assertSessionHas('success');

        // Verifikasi stok berkurang 1
        $this->assertDatabaseHas('bukus', [
            'id'   => $this->bukuNormal->id,
            'stok' => $stokAwal - 1,
        ]);

        // Verifikasi record peminjaman ada
        $this->assertDatabaseHas('peminjamans', [
            'buku_id'      => $this->bukuNormal->id,
            'nim_peminjam' => '12S24028',
            'status'       => 'dipinjam',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // 2. PENOLAKAN STOK = 0 (ACID Consistency)
    // ────────────────────────────────────────────────────────────

    public function test_peminjaman_ditolak_saat_stok_habis(): void
    {
        // Pastikan stok memang 0
        $this->assertEquals(0, $this->bukuStokNol->fresh()->stok);

        $response = $this->actingAs($this->pustakawan)->post(
            route('peminjamans.store', $this->bukuStokNol),
            [
                'nim_peminjam'        => '12S24099',
                'nama_peminjam'       => 'Test Penolakan',
                'tanggal_pinjam'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            ]
        );

        // Harus redirect balik dengan error session
        $response->assertSessionHas('error');

        // Stok tetap 0 — tidak ada perubahan
        $this->assertDatabaseHas('bukus', [
            'id'   => $this->bukuStokNol->id,
            'stok' => 0,
        ]);

        // Tidak ada record peminjaman yang dibuat
        $this->assertDatabaseMissing('peminjamans', [
            'buku_id'      => $this->bukuStokNol->id,
            'nim_peminjam' => '12S24099',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // 3. PENOLAKAN DUPLIKAT NIM (ACID Consistency)
    // ────────────────────────────────────────────────────────────

    public function test_peminjaman_duplikat_nim_ditolak(): void
    {
        // Pinjam buku normal dengan stok awal cukup
        $bukuBanyak = Buku::where('isbn', '9780062315007')->first(); // stok 15

        // Pinjam pertama
        $this->actingAs($this->pustakawan)->post(
            route('peminjamans.store', $bukuBanyak),
            [
                'nim_peminjam'        => '12S24028',
                'nama_peminjam'       => 'Test Mahasiswa',
                'tanggal_pinjam'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            ]
        );

        // Pinjam kedua dengan NIM yang sama dan buku yang sama
        $response = $this->actingAs($this->pustakawan)->post(
            route('peminjamans.store', $bukuBanyak),
            [
                'nim_peminjam'        => '12S24028',
                'nama_peminjam'       => 'Test Mahasiswa',
                'tanggal_pinjam'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            ]
        );

        $response->assertSessionHas('error');

        // Hanya ada 1 record aktif untuk kombinasi NIM + buku ini
        $count = Peminjaman::where('buku_id', $bukuBanyak->id)
            ->where('nim_peminjam', '12S24028')
            ->where('status', 'dipinjam')
            ->count();
        $this->assertEquals(1, $count);
    }

    // ────────────────────────────────────────────────────────────
    // 4. PENGEMBALIAN BERHASIL (Stok Bertambah Atomik)
    // ────────────────────────────────────────────────────────────

    public function test_pengembalian_berhasil_dan_stok_bertambah(): void
    {
        $bukuBanyak = Buku::where('isbn', '9780132350884')->first(); // Clean Code stok 25 (dikurangi seeder)

        // Buat peminjaman manual
        $peminjaman = Peminjaman::create([
            'kode_pinjam'         => 'PJM-TEST9999',
            'buku_id'             => $bukuBanyak->id,
            'nim_peminjam'        => '12S24099',
            'nama_peminjam'       => 'Tester Return',
            'tanggal_pinjam'      => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status'              => 'dipinjam',
        ]);
        $stokSebelum = $bukuBanyak->fresh()->stok;

        $response = $this->actingAs($this->pustakawan)->patch(
            route('peminjamans.kembalikan', $peminjaman)
        );

        $response->assertRedirect(route('peminjamans.index'));
        $response->assertSessionHas('success');

        // Stok bertambah 1
        $this->assertDatabaseHas('bukus', [
            'id'   => $bukuBanyak->id,
            'stok' => $stokSebelum + 1,
        ]);

        // Status berubah jadi dikembalikan
        $this->assertDatabaseHas('peminjamans', [
            'id'     => $peminjaman->id,
            'status' => 'dikembalikan',
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // 5. PENOLAKAN PENGEMBALIAN GANDA
    // ────────────────────────────────────────────────────────────

    public function test_pengembalian_ganda_ditolak(): void
    {
        $peminjaman = Peminjaman::create([
            'kode_pinjam'         => 'PJM-TESTDUPL',
            'buku_id'             => Buku::first()->id,
            'nim_peminjam'        => '12S24077',
            'nama_peminjam'       => 'Tester Duplikat',
            'tanggal_pinjam'      => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status'              => 'dikembalikan', // sudah dikembalikan
            'tanggal_kembali'     => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->pustakawan)->patch(
            route('peminjamans.kembalikan', $peminjaman)
        );

        $response->assertSessionHas('error');
    }
}
