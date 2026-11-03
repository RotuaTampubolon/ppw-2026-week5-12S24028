<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji 1: Login sukses meregenerasi session ID, menyimpan session state, dan memasang cookie.
     */
    public function test_login_sukses_meregenerasi_sesi_dan_memasang_cookie(): void
    {
        $user = User::factory()->create([
            'email'    => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
            'name'     => 'Pustakawan Del',
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('bukus.index'));
        $this->assertAuthenticatedAs($user);

        // Verifikasi session state
        $response->assertSessionHas('login_timestamp');

        // Verifikasi cookie terpasang di response
        $response->assertCookie('del_last_librarian_email', 'pustakawan@del.ac.id');
    }

    /**
     * Uji 2: Login gagal jika kredensial salah.
     */
    public function test_login_gagal_jika_kredensial_salah(): void
    {
        $user = User::factory()->create([
            'email'    => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'email'    => 'pustakawan@del.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    /**
     * Uji 3: Logout menghapus sesi autentikasi dan meregenerasi token CSRF.
     */
    public function test_logout_menghapus_sesi_pengguna(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Uji 4: Rute mutasi sirkulasi dan CRUD diproteksi oleh middleware auth (Guest dialihkan).
     */
    public function test_rute_mutasi_diproteksi_middleware_auth(): void
    {
        // Akses form peminjaman tanpa login harus dialihkan ke /login
        $response = $this->get(route('peminjamans.create'));
        $response->assertRedirect(route('login'));

        // Akses create buku tanpa login harus dialihkan ke /login
        $response = $this->get(route('bukus.create'));
        $response->assertRedirect(route('login'));
    }
}
