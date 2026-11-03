<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeminjamanAcidTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'pustakawan@del.ac.id',
            'name'  => 'Pustakawan Del',
        ]);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KAT-TEST',
            'nama_kategori' => 'Kategori Pengujian',
        ]);
    }

    /**
     * Uji 1: Peminjaman normal berhasil mengurangi stok secara atomik.
     */
    public function test_peminjaman_buku_berhasil_dan_mengurangi_stok_secara_atomik(): void
    {
        $buku = Buku::create([
            'isbn'         => '9781111111111',
            'judul'        => 'Buku Uji Normal',
            'penulis'      => 'Penulis Uji',
            'penerbit'     => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 5,
        ]);

        $response = $this->actingAs($this->user)->post(route('peminjamans.store'), [
            'buku_id'       => $buku->id,
            'nim_peminjam'  => '12S24028',
            'nama_peminjam' => 'Rotua Tampubolon',
            'durasi_hari'   => 7,
        ]);

        $response->assertRedirect(route('peminjamans.index'));
        $response->assertSessionHas('success');

        // Verifikasi stok berkurang menjadi 4
        $this->assertEquals(4, $buku->fresh()->stok);

        // Verifikasi record peminjaman tercipta
        $this->assertDatabaseHas('peminjamans', [
            'buku_id'      => $buku->id,
            'nim_peminjam' => '12S24028',
            'status'       => 'dipinjam',
        ]);
    }

    /**
     * Uji 2: Penolakan dan Rollback Transaksi ACID saat stok buku = 0.
     */
    public function test_peminjaman_buku_stok_nol_ditolak_dan_dirollback(): void
    {
        $buku = Buku::create([
            'isbn'         => '9782222222222',
            'judul'        => 'Buku Uji Stok Nol',
            'penulis'      => 'Penulis Uji',
            'penerbit'     => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 0, // Stok habis
        ]);

        $response = $this->actingAs($this->user)->from(route('peminjamans.create'))->post(route('peminjamans.store'), [
            'buku_id'       => $buku->id,
            'nim_peminjam'  => '12S24028',
            'nama_peminjam' => 'Rotua Tampubolon',
        ]);

        $response->assertRedirect(route('peminjamans.create'));
        $response->assertSessionHas('error');

        // Pastikan stok tetap 0 (tidak menjadi -1)
        $this->assertEquals(0, $buku->fresh()->stok);

        // Pastikan tidak ada transaksi yang terbuat
        $this->assertDatabaseMissing('peminjamans', [
            'buku_id'      => $buku->id,
            'nim_peminjam' => '12S24028',
        ]);
    }

    /**
     * Uji 3: Penolakan peminjaman ganda oleh NIM yang sama untuk buku yang belum dikembalikan.
     */
    public function test_nim_sama_tidak_bisa_meminjam_buku_yang_sama_sebelum_dikembalikan(): void
    {
        $buku = Buku::create([
            'isbn'         => '9783333333333',
            'judul'        => 'Buku Uji Duplikasi NIM',
            'penulis'      => 'Penulis Uji',
            'penerbit'     => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 3,
        ]);

        // Peminjaman pertama
        $this->actingAs($this->user)->post(route('peminjamans.store'), [
            'buku_id'       => $buku->id,
            'nim_peminjam'  => '12S24028',
            'nama_peminjam' => 'Rotua Tampubolon',
        ]);

        $this->assertEquals(2, $buku->fresh()->stok);

        // Peminjaman kedua oleh NIM yang sama sebelum buku dikembalikan
        $response = $this->actingAs($this->user)->from(route('peminjamans.create'))->post(route('peminjamans.store'), [
            'buku_id'       => $buku->id,
            'nim_peminjam'  => '12S24028',
            'nama_peminjam' => 'Rotua Tampubolon',
        ]);

        $response->assertRedirect(route('peminjamans.create'));
        $response->assertSessionHas('error');

        // Stok tidak boleh berkurang lagi
        $this->assertEquals(2, $buku->fresh()->stok);
    }

    /**
     * Uji 4: Pengembalian buku memulihkan stok dan mengisi tanggal kembali secara atomik.
     */
    public function test_pengembalian_buku_memulihkan_stok_dan_mengubah_status(): void
    {
        $buku = Buku::create([
            'isbn'         => '9784444444444',
            'judul'        => 'Buku Uji Pengembalian',
            'penulis'      => 'Penulis Uji',
            'penerbit'     => 'Penerbit Uji',
            'tahun_terbit' => 2024,
            'kategori_id'  => $this->kategori->id,
            'stok'         => 2,
        ]);

        $pinjam = Peminjaman::create([
            'kode_pinjam'         => 'PINJ-TEST-RETURN',
            'buku_id'             => $buku->id,
            'nim_peminjam'        => '12S24028',
            'nama_peminjam'       => 'Rotua Tampubolon',
            'tanggal_pinjam'      => now()->subDays(3)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
            'status'              => 'dipinjam',
        ]);

        // Eksekusi pengembalian
        $response = $this->actingAs($this->user)->patch(route('peminjamans.kembali', $pinjam->id));

        $response->assertSessionHas('success');

        // Status berubah menjadi dikembalikan
        $this->assertEquals('dikembalikan', $pinjam->fresh()->status);
        $this->assertNotNull($pinjam->fresh()->tanggal_kembali);

        // Stok bertambah kembali menjadi 3
        $this->assertEquals(3, $buku->fresh()->stok);
    }
}
