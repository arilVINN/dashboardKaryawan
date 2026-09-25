<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Cek nama role sesuai dengan relasi di database
        if (!in_array($user->role->nama_role, $roles)) {
            return response()->json(['message' => 'Forbidden. Akses ditolak.'], 403);
        }

        return $next($request);
    }
}