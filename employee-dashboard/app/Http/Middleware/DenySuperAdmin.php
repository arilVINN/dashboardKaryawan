<?php

namespace App\Http\Middleware;

use App\Models\User2;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenySuperAdmin
{
    /**
     * The Direksi/Pemimpin super-admin role is read-only when it comes to
     * task mutations: creating, editing, deleting and reviewing tasks is
     * reserved for Kadiv. This middleware blocks those write actions while
     * leaving the read (monitoring) endpoints untouched.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User2 && $user->isSuperAdmin()) {
            $message = 'Direksi hanya dapat memantau tugas (read-only).';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
