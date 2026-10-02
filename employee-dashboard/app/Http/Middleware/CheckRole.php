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

        // Case-insensitive comparison so 'Staff', 'staff', 'STAFF' all match.
        $userRole = strtolower($user->role?->nama_role ?? '');

        if (!in_array($userRole, array_map('strtolower', $roles), true)) {
            return response()->json(['message' => 'Forbidden. Akses ditolak.'], 403);
        }

        return $next($request);
    }
}
