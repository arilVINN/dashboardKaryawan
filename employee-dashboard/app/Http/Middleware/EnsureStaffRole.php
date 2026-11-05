<?php

namespace App\Http\Middleware;

use App\Models\User2;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffRole
{
    /**
     * Mapping role → named route dashboard masing-masing.
     */
    private const DASHBOARD_ROUTES = [
        'hrd'   => 'hrd.dashboard',
        'kadiv' => 'kadiv.dashboard',
        'staff' => 'dashboard',
    ];

    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'User staff belum terautentikasi.'], 401);
            }
            return redirect()->route('login');
        }

        $user->loadMissing('role');

        if (strtolower($user->role?->nama_role ?? '') !== 'staff') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses hanya diberikan kepada Staff.'], 403);
            }

            $userRole       = strtolower($user->role?->nama_role ?? '');
            $dashboardRoute = self::DASHBOARD_ROUTES[$userRole] ?? 'login';

            return redirect()->route($dashboardRoute)
                ->with('forbidden', 'Akses ditolak. Anda telah diarahkan ke dashboard Anda.');
        }

        return $next($request);
    }
}
