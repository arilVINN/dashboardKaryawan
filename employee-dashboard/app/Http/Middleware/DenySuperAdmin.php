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
     * deleting company data (tugas, karyawan, divisi, jabatan, struktur
     * organisasi, keuangan) is reserved for HRD/Kadiv/superadmin. Finance
     * approvals and verifications are reserved for Kadiv Finance, so this
     * middleware also blocks those write actions. Read (monitoring) and
     * messaging endpoints are left untouched.
     *
     * The literal "superadmin" role is deliberately NOT blocked: despite
     * sharing Direktur-level read access, it is the one account allowed to
     * write everywhere.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User2 && in_array(
            strtolower($user->role?->nama_role ?? ''),
            ['direktur', 'direksi'],
            true
        )) {
            $message = 'Direksi hanya dapat memantau data (read-only).';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
