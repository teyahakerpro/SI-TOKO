<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Memeriksa apakah user memiliki peran yang diizinkan untuk rute ini.
     * Penggunaan di rute: ->middleware('role:admin') atau ->middleware('role:admin,kasir')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Jika belum login, redirect ke halaman login
        if (! $user) {
            return redirect()->route('login');
        }

        // Cek apakah peran user ada di daftar roles yang diizinkan
        if (! in_array($user->peran, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.',
                ], 403);
            }

            // Jika akses web biasa ditolak, arahkan ke katalog produk dengan pesan peringatan
            return redirect()->route('produk.index')->with('error', 'Akses ditolak: Area ini hanya untuk peran ' . implode('/', $roles) . '.');
        }

        return $next($request);
    }
}

