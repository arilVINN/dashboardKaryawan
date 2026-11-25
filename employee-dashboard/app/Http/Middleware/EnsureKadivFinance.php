<?php

namespace App\Http\Middleware;

use App\Models\User2;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware untuk memproteksi rute khusus Kepala Divisi Finance.
 *
 * Kriteria akses: role = 'kadiv' DAN divisi adalah unit Finance & Accounting.
 * Rute ini hanya boleh diakses oleh Kadiv Finance, bukan Kadiv divisi lain.
 * Direktur/Direksi memiliki akses baca (monitoring); aksi tulis/approve
 * diblokir oleh middleware `deny.superadmin` dan policy keuangan.
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

        // Pastikan role adalah kadiv Finance (kode FIN) atau Direktur
        if (! $user->isKadivFinance() && ! $user->isDirektur()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. Akses ini khusus Kepala Divisi Finance & Direktur.',
                ], 403);
            }

            $redirectRoute = $user->isDirektur() ? 'direktur.dashboard' : 'kadiv.dashboard';
            return redirect()->route($redirectRoute)
                ->with('forbidden', 'Akses ini khusus Kepala Divisi Finance & Accounting.');
        }

        return $next($request);
    }
}
