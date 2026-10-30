<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /** Jumlah percobaan gagal sebelum dikunci sementara. */
    private const MAKS_PERCOBAAN = 5;

    /** Lama kunci dalam detik (1 menit). */
    private const DURASI_KUNCI = 60;

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'Nama pengguna wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $kunci = $this->kunciPercobaan($request);

        if (RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            $detik = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
            ]);
        }

        // Dicocokkan ke tabel karyawan (kolom username & password_hash).
        $berhasil = Auth::attempt(
            ['username' => $data['username'], 'password' => $data['password']],
            $request->boolean('remember')
        );

        if (! $berhasil) {
            RateLimiter::hit($kunci, self::DURASI_KUNCI);

            throw ValidationException::withMessages([
                'username' => 'Nama pengguna atau kata sandi salah.',
            ]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        // Sementara Owner dan Kasir sama-sama diarahkan ke katalog produk.
        return redirect()->intended(route('produk.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah keluar dari sistem.');
    }

    /** Kunci percobaan per kombinasi username + IP, supaya satu user tidak memblokir yang lain. */
    private function kunciPercobaan(Request $request): string
    {
        return 'login|' . strtolower((string) $request->input('username')) . '|' . $request->ip();
    }
}
