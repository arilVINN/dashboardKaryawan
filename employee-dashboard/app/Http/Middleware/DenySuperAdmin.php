<?php

namespace App\Http\Middleware;

use App\Models\User2;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenySuperAdmin
{
    /**
     * The Direksi/Pemimpin super-admin role is a read-only monitor: creating,
     * editing and deleting company data (tugas, karyawan, divisi) is reserved
     * for HRD/Kadiv. This middleware blocks those write actions while leaving
     * the read (monitoring) and messaging endpoints untouched.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User2 && $user->isSuperAdmin()) {
            $message = 'Direksi hanya dapat memantau data (read-only).';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
