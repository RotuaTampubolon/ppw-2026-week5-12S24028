<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan form login pustakawan.
     * Mengambil cookie 'del_last_librarian_email' untuk kenyamanan UX.
     */
    public function showLoginForm(Request $request): View
    {
        $rememberedEmail = Cookie::get('del_last_librarian_email', '');

        return view('auth.login', compact('rememberedEmail'));
    }

    /**
     * Memproses autentikasi pustakawan, regenerasi ID sesi (anti-Session Fixation),
     * mencatat timestamp sesi, dan mengantrekan cookie aman.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // WAJIB: Regenerasi Session ID untuk mitigasi Session Fixation Attack
            $request->session()->regenerate();

            // Simpan timestamp autentikasi ke dalam Session Server
            session([
                'login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB',
            ]);

            // Queue HTTP Cookie (HttpOnly) selama 7 hari (10.080 menit)
            Cookie::queue('del_last_librarian_email', $request->email, 10080);

            return redirect()->intended(route('bukus.index'))
                ->with('success', 'Autentikasi berhasil! Sesi Pustakawan aktif.');
        }

        return back()->withInput($request->only('email'))
            ->with('error', 'Kredensial login tidak valid. Silakan periksa email dan password Anda.');
    }

    /**
     * Logout pustakawan, memusnahkan sesi server (invalidate),
     * dan meregenerasi CSRF token.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate session data di server
        $request->session()->invalidate();

        // Regenerasi CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi pustakawan telah ditutup dengan aman.');
    }
}
