<?php

namespace App\Http\Middleware;

use App\Models\User2;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffRole
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $user->loadMissing('role');

        if (strtolower($user->role?->nama_role ?? '') !== 'staff') {
            return response()->json([
                'message' => 'Akses hanya diberikan kepada Staff.',
            ], 403);
        }

        return $next($request);
    }
}
