<?php

use App\Http\Middleware\ApiResponseFormatter;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\DenySuperAdmin;
use App\Http\Middleware\EnsureAccountSecurity;
use App\Http\Middleware\EnsureKadivFinance;
use App\Http\Middleware\EnsureTokenAccountSecurity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust only explicitly configured proxies. Trusting "*" lets a client
        // spoof X-Forwarded-For, which resets IP-keyed login throttling.
        $trustedProxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1,::1'))
        )));

        $middleware->trustProxies(at: $trustedProxies);

        $middleware->validateCsrfTokens(except: [
            'login',
            'api/login',
            'staff/pesan/send',
        ]);

        $middleware->alias([
            'role' => CheckRole::class,
            'kadiv.finance' => EnsureKadivFinance::class,
            'deny.superadmin' => DenySuperAdmin::class,
            'gateway.throttle' => ThrottleRequests::class,
            'gateway.format' => ApiResponseFormatter::class,
            'akun.aman' => EnsureAccountSecurity::class,
            'akun.aman.api' => EnsureTokenAccountSecurity::class,
        ]);

        // Runs on every session-authenticated request. No-op for users without
        // the forced-change flag or a stale session version.
        $middleware->web(append: ['akun.aman']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $statusCode = 500;
                $message = 'Server Error';
                $data = null;

                if ($e instanceof AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Unauthenticated';
                } elseif ($e instanceof AuthorizationException) {
                    $statusCode = 403;
                    $message = 'Forbidden';
                } elseif ($e instanceof ValidationException) {
                    $statusCode = 422;
                    $message = 'Validation Failed';
                    $data = $e->errors();
                } elseif ($e instanceof ModelNotFoundException) {
                    $statusCode = 404;
                    $message = 'Resource not found';
                } elseif ($e instanceof PostTooLargeException) {
                    $statusCode = 413;
                    $message = 'Ukuran file terlalu besar. Maksimal yang diizinkan adalah 200 MB.';
                } else {
                    $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                    $message = $statusCode >= 500 ? 'Server Error' : 'Request failed';
                }

                return response()->json([
                    'success' => false,
                    'code' => $statusCode,
                    'message' => $message,
                    'data' => $data,
                    // Keep the conventional Laravel shape too so clients can read
                    // field-level validation messages without knowing about `data`.
                    'errors' => $e instanceof ValidationException ? $data : null,
                ], $statusCode);
            }
            // Helper untuk redirect pengguna browser ke dashboard peran masing-masing dengan pesan toast
            $redirectToDashboard = function (string $key, string $message) use ($request) {
                $user = $request->user() ?: auth()->user();
                $role = strtolower($user?->role?->nama_role ?? '');

                $dashboardRoute = match ($role) {
                    'direktur', 'direksi' => 'direktur.dashboard',
                    'hrd' => 'hrd.dashboard',
                    'kadiv' => 'kadiv.dashboard',
                    'staff' => 'dashboard',
                    default => 'login',
                };

                if ($dashboardRoute === 'login') {
                    // Jika user tidak terautentikasi, selalu tampilkan pesan login generic
                    $message = 'Silakan login terlebih dahulu untuk mengakses sistem.';
                }

                $targetUrl = route($dashboardRoute);
                if ($request->url() === $targetUrl) {
                    // Already on the destination. Redirecting again (including
                    // back()) can produce ERR_TOO_MANY_REDIRECTS when the
                    // destination itself is the source of the error.
                    return response()->view('errors.loop', ['message' => $message], 500);
                }

                return redirect()->route($dashboardRoute)->with($key, $message);
            };

            $is419 = $e instanceof TokenMismatchException
                || (method_exists($e, 'getStatusCode') && $e->getStatusCode() === 419);

            if ($is419) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json(['message' => 'CSRF token mismatch / Page Expired.'], 419);
                }

                return redirect()->route('login')->with('error', 'Sesi telah kadaluarsa (Page Expired). Silakan login kembali.');
            }

            if ($e instanceof ThrottleRequestsException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json([
                        'success' => false,
                        'code' => 429,
                        'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat sebelum mencoba kembali.',
                    ], 429, $e->getHeaders());
                }

                if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
                    return back()->withInput()->with('error', 'Terlalu banyak percobaan. Harap tunggu beberapa saat sebelum mencoba kembali.');
                }

                return $redirectToDashboard('error', 'Terlalu banyak permintaan. Harap tunggu beberapa saat sebelum mencoba kembali.');
            }

            if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json(['message' => 'Data atau halaman tidak ditemukan.'], 404);
                }

                return $redirectToDashboard('error', 'Halaman atau data tidak ditemukan. Anda telah diarahkan ke dashboard Anda.');
            }

            if ($e instanceof AccessDeniedHttpException || $e instanceof AuthorizationException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json(['message' => 'Forbidden. Akses ditolak.'], 403);
                }

                return $redirectToDashboard('forbidden', 'Akses ditolak. Anda telah diarahkan ke dashboard Anda.');
            }

            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            if ($statusCode >= 500 && ! ($e instanceof ValidationException)) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json([
                        'success' => false,
                        'code' => 500,
                        'message' => 'Terjadi kesalahan pada server.',
                    ], 500);
                }

                return $redirectToDashboard('error', 'Terjadi kendala pada sistem. Harap coba lagi');
            }
        });
    })->create();
