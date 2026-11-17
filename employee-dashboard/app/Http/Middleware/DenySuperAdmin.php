<?php

namespace App\Http\Middleware;

use App\Models\User2;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenySuperAdmin
{
    /**
     * The Direktur/Direksi role is a read-only monitor: creating, editing and
     * deleting company data (tugas, karyawan, divisi, keuangan) is reserved for
     * HRD/Kadiv. This middleware blocks those write actions while leaving the
     * read (monitoring), approval and messaging endpoints untouched.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User2 && $user->isDirektur()) {
            $message = 'Direksi hanya dapat memantau data (read-only).';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
