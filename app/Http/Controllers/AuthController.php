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
     * Tampilkan form login pustakawan.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi pustakawan.
     * - Auth::attempt() dengan remember-me
     * - session()->regenerate() untuk mencegah Session Fixation Attack
     * - Simpan login_timestamp ke session
     * - Queue cookie del_last_user_email (HttpOnly, 7 hari)
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // WAJIB: regenerate session ID untuk mencegah Session Fixation Attack
            $request->session()->regenerate();

            // Simpan timestamp login ke session
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']);

            // Queue cookie del_last_user_email — HttpOnly, expired 7 hari (10080 menit)
            Cookie::queue('del_last_user_email', $request->email, 10080);

            return redirect()->intended(route('bukus.index'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '! Anda berhasil login.');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->withInput($request->only('email', 'remember'))
          ->with('error', 'Kredensial login tidak valid.');
    }

    /**
     * Proses logout pustakawan.
     * - Auth::logout()
     * - session()->invalidate() untuk menghapus seluruh data session
     * - session()->regenerateToken() untuk memperbarui CSRF token
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout dari SIPUS-Del.');
    }
}
