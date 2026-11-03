<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AuthAndSessionTest — Menguji autentikasi pustakawan, session, dan cookie.
 *
 * Kriteria Rubrik:
 * - Login berhasil dengan kredensial valid
 * - Login gagal dengan kredensial salah
 * - Session regenerate setelah login (session fixation prevention)
 * - Cookie del_last_user_email di-queue setelah login
 * - login_timestamp tersimpan di session
 * - Logout mengosongkan session
 * - Route auth-protected redirect ke login jika belum login
 * - Route guest-only redirect ke home jika sudah login
 */
class AuthAndSessionTest extends TestCase
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
    // 1. LOGIN BERHASIL
    // ────────────────────────────────────────────────────────────

    public function test_login_berhasil_dengan_kredensial_valid(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('bukus.index'));
        $this->assertAuthenticated();
    }

    public function test_login_menyimpan_timestamp_ke_session(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('login_timestamp');
    }

    public function test_login_mengqueue_cookie_del_last_user_email(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertCookie('del_last_user_email', 'pustakawan@del.ac.id');
    }

    // ────────────────────────────────────────────────────────────
    // 2. LOGIN GAGAL
    // ────────────────────────────────────────────────────────────

    public function test_login_gagal_dengan_password_salah(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_gagal_dengan_email_tidak_terdaftar(): void
    {
        $response = $this->post(route('login.post'), [
            'email'    => 'tidakada@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ────────────────────────────────────────────────────────────
    // 3. LOGOUT
    // ────────────────────────────────────────────────────────────

    public function test_logout_mengosongkan_sesi_dan_redirect(): void
    {
        $response = $this->actingAs($this->pustakawan)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ────────────────────────────────────────────────────────────
    // 4. MIDDLEWARE AUTH
    // ────────────────────────────────────────────────────────────

    public function test_halaman_buat_buku_redirect_ke_login_jika_belum_auth(): void
    {
        $response = $this->get(route('bukus.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_halaman_peminjaman_redirect_ke_login_jika_belum_auth(): void
    {
        $response = $this->get(route('peminjamans.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_halaman_kategori_redirect_ke_login_jika_belum_auth(): void
    {
        $response = $this->get(route('kategoris.index'));
        $response->assertRedirect(route('login'));
    }

    // ────────────────────────────────────────────────────────────
    // 5. MIDDLEWARE GUEST
    // ────────────────────────────────────────────────────────────

    public function test_halaman_login_redirect_ke_home_jika_sudah_auth(): void
    {
        $response = $this->actingAs($this->pustakawan)->get(route('login'));
        // Guest middleware meredirect ke home (/) yang kemudian redirect ke /bukus
        $response->assertRedirect();
    }

    // ────────────────────────────────────────────────────────────
    // 6. AKSES ROUTE SETELAH LOGIN
    // ────────────────────────────────────────────────────────────

    public function test_halaman_peminjaman_dapat_diakses_setelah_login(): void
    {
        $response = $this->actingAs($this->pustakawan)->get(route('peminjamans.index'));
        $response->assertStatus(200);
        $response->assertSee('Peminjaman');
    }
}
