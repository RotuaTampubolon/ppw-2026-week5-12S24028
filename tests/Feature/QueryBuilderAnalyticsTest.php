<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * QueryBuilderAnalyticsTest — Menguji fitur Query Builder dan filter dinamis.
 *
 * Kriteria Rubrik:
 * - DB::table() untuk statistik (total_judul, total_stok, dll.)
 * - withCount('peminjamans') tampil di halaman buku
 * - Filter pencarian dengan when() (judul, penulis, ISBN)
 * - Filter kategori dengan when()
 * - Session last_buku_search tersimpan
 * - Pagination withQueryString()
 */
class QueryBuilderAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $pustakawan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->pustakawan = User::where('email', 'pustakawan@del.ac.id')->first();
    }

    // ────────────────────────────────────────────────────────────
    // 1. STATISTIK QUERY BUILDER
    // ────────────────────────────────────────────────────────────

    public function test_statistik_query_builder_tampil_di_halaman_buku(): void
    {
        $response = $this->get(route('bukus.index'));

        $response->assertStatus(200);

        // Nilai dari DB::table('bukus')->count()
        $totalJudul = DB::table('bukus')->count();
        $response->assertSee((string) $totalJudul);
    }

    public function test_db_table_query_builder_mengembalikan_nilai_benar(): void
    {
        $totalJudul        = DB::table('bukus')->count();
        $totalStok         = DB::table('bukus')->sum('stok');
        $totalDipinjam     = DB::table('peminjamans')->where('status', 'dipinjam')->count();
        $totalDikembalikan = DB::table('peminjamans')->where('status', 'dikembalikan')->count();

        $this->assertGreaterThan(0, $totalJudul);
        $this->assertGreaterThan(0, $totalStok);
        $this->assertGreaterThanOrEqual(0, $totalDipinjam);
        $this->assertGreaterThanOrEqual(0, $totalDikembalikan);
    }

    // ────────────────────────────────────────────────────────────
    // 2. FILTER PENCARIAN DINAMIS (when())
    // ────────────────────────────────────────────────────────────

    public function test_filter_pencarian_judul_menggunakan_when(): void
    {
        $response = $this->get(route('bukus.index', ['search' => 'Clean Code']));

        $response->assertStatus(200);
        $response->assertSee('Clean Code');
    }

    public function test_filter_pencarian_penulis_menggunakan_when(): void
    {
        $response = $this->get(route('bukus.index', ['search' => 'Tolkien']));

        $response->assertStatus(200);
        $response->assertSee('Tolkien');
    }

    public function test_filter_pencarian_isbn_menggunakan_when(): void
    {
        $response = $this->get(route('bukus.index', ['search' => '9780547928227']));

        $response->assertStatus(200);
        $response->assertSee('9780547928227');
    }

    public function test_filter_kategori_menggunakan_when(): void
    {
        $kategori = Kategori::where('kode_kategori', 'KAT-FIK')->first();

        $response = $this->get(route('bukus.index', ['kategori_id' => $kategori->id]));

        $response->assertStatus(200);
        $response->assertSee('Fiksi');
    }

    public function test_pencarian_tidak_ada_hasil(): void
    {
        $response = $this->get(route('bukus.index', ['search' => 'ZZZNOTEXIST999']));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada buku yang sesuai');
    }

    // ────────────────────────────────────────────────────────────
    // 3. SESSION LAST BUKU SEARCH
    // ────────────────────────────────────────────────────────────

    public function test_pencarian_menyimpan_keyword_ke_session(): void
    {
        $response = $this->get(route('bukus.index', ['search' => 'Laravel']));

        $response->assertSessionHas('last_buku_search', 'Laravel');
    }

    // ────────────────────────────────────────────────────────────
    // 4. WITHCOUNT PEMINJAMANS
    // ────────────────────────────────────────────────────────────

    public function test_kolom_withcount_peminjamans_muncul_di_halaman(): void
    {
        $response = $this->get(route('bukus.index'));

        $response->assertStatus(200);
        // Kolom "Dipinjam" dengan badge count harus ada
        $response->assertSee('Dipinjam');
    }
}
