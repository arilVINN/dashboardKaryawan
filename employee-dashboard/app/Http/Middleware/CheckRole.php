<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Mapping role → named route dashboard masing-masing.
     */
    private const DASHBOARD_ROUTES = [
        'hrd'   => 'hrd.dashboard',
        'kadiv' => 'kadiv.dashboard',
        'staff' => 'dashboard',
    ];

    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Case-insensitive comparison so 'Staff', 'staff', 'STAFF' all match.
        $userRole = strtolower($user->role?->nama_role ?? '');

        if (! in_array($userRole, array_map('strtolower', $roles), true)) {
            // Request AJAX / API → tetap JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Akses ditolak.'], 403);
            }

            // Request browser → redirect ke dashboard role user yg sedang login
            $dashboardRoute = self::DASHBOARD_ROUTES[$userRole] ?? 'login';

            return redirect()->route($dashboardRoute)
                ->with('forbidden', 'Akses ditolak. Anda telah diarahkan ke dashboard Anda.');
        }

        return $next($request);
    }
}
