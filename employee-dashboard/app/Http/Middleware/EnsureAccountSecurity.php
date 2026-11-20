<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Forces a password change on the first login after an administrator issued
 * a temporary password, and invalidates sessions whose version no longer
 * matches the user's stored version.
 */
class EnsureAccountSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $sessionVersion = $request->session()->get('session_version');
        if ($sessionVersion !== null && $sessionVersion !== $user->session_version) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'username' => 'Sesi Anda telah berakhir. Silakan masuk kembali.',
            ]);
        }

        if ($user->must_change_password
            && ! $request->routeIs('password.change', 'password.change.store', 'logout')) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
