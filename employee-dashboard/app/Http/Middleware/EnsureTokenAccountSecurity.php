<?php

namespace App\Http\Middleware;

use App\Models\User2;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API equivalent of EnsureAccountSecurity.
 *
 * Bearer tokens have no session_version to compare, so every token request
 * re-checks that the account is still active and is not using a temporary
 * password that must be changed first.
 */
class EnsureTokenAccountSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return $next($request);
        }

        if (($user->karyawan?->status_aktif ?? 'Aktif') === 'Tidak Aktif') {
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Akun Anda dinonaktifkan. Hubungi HRD untuk informasi lebih lanjut.',
            ], 403);
        }

        if ($user->must_change_password) {
            return response()->json([
                'message' => 'Password sementara masih berlaku. Ganti password melalui halaman web terlebih dahulu.',
            ], 403);
        }

        return $next($request);
    }
}
