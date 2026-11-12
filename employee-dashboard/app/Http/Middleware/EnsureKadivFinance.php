<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware untuk memproteksi rute khusus Kepala Divisi Finance.
 *
 * Kriteria akses: role = 'kadiv' DAN divisi kode_divisi = 'FIN'.
 * Rute ini hanya boleh diakses oleh Kadiv Finance, bukan Kadiv divisi lain.
 */
class EnsureKadivFinance
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Pastikan role adalah kadiv DAN divisi adalah Finance (kode FIN)
        if (! $user->isKadivFinance()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Akses ini khusus Kepala Divisi Finance.',
                ], 403);
            }

            return redirect()->route('kadiv.dashboard')
                ->with('forbidden', 'Akses ini khusus Kepala Divisi Finance & Accounting.');
        }

        return $next($request);
    }
}
