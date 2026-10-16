<?php

namespace App\Http\Middleware;

use App\Models\User2;
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
        // The Direksi/Pemimpin super-admin role also satisfies every HRD and
        // Kadiv gate (see User2::hasAnyRole()).
        if (! $user instanceof User2 || ! $user->hasAnyRole($roles)) {
            return response()->json(['message' => 'Forbidden. Akses ditolak.'], 403);
        }

        return $next($request);
    }
}
