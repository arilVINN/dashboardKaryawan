<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'login',
            'api/login',
            'staff/pesan/send',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'gateway.throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'gateway.format' => \App\Http\Middleware\ApiResponseFormatter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                $statusCode = 500;
                $message = 'Server Error';
                $data = null;

                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Unauthenticated';
                } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $statusCode = 403;
                    $message = 'Forbidden';
                } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;
                    $message = 'Validation Failed';
                    $data = $e->errors();
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $statusCode = 404;
                    $message = 'Resource not found';
                } elseif ($e instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
                    $statusCode = 413;
                    $message = 'Ukuran file terlalu besar. Maksimal yang diizinkan adalah 200 MB.';
                } else {
                    $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                    $message = $statusCode >= 500 ? 'Server Error' : 'Request failed';
                }

                return response()->json([
                    'success' => false,
                    'code'    => $statusCode,
                    'message' => $message,
                    'data'    => $data,
                    // Keep the conventional Laravel shape too so clients can read
                    // field-level validation messages without knowing about `data`.
                    'errors'  => $e instanceof \Illuminate\Validation\ValidationException ? $data : null,
                ], $statusCode);
            }
            // Helper untuk redirect pengguna browser ke dashboard peran masing-masing dengan pesan toast
            $redirectToDashboard = function (string $key, string $message) use ($request) {
                $user = $request->user() ?: auth()->user();
                $role = strtolower($user?->role?->nama_role ?? '');

                $dashboardRoute = match ($role) {
                    'hrd'   => 'hrd.dashboard',
                    'kadiv' => 'kadiv.dashboard',
                    'staff' => 'dashboard',
                    default => 'login',
                };

                $targetUrl = route($dashboardRoute);
                if ($request->url() === $targetUrl) {
                    return null;
                }

                return redirect()->route($dashboardRoute)->with($key, $message);
            };

            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json(['message' => 'CSRF token mismatch.'], 419);
                }
                return redirect()->route('login')->withErrors(['message' => 'Sesi telah kadaluarsa. Silakan login kembali.']);
            }

            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json([
                        'success' => false,
                        'code'    => 429,
                        'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat sebelum mencoba kembali.',
                    ], 429, $e->getHeaders());
                }

                if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
                    return back()->withInput()->with('error', 'Terlalu banyak percobaan. Harap tunggu beberapa saat sebelum mencoba kembali.');
                }

                return $redirectToDashboard('error', 'Terlalu banyak permintaan. Harap tunggu beberapa saat sebelum mencoba kembali.');
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json(['message' => 'Data atau halaman tidak ditemukan.'], 404);
                }

                return $redirectToDashboard('error', 'Halaman atau data tidak ditemukan. Anda telah diarahkan ke dashboard Anda.');
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json(['message' => 'Forbidden. Akses ditolak.'], 403);
                }

                return $redirectToDashboard('forbidden', 'Akses ditolak. Anda telah diarahkan ke dashboard Anda.');
            }

            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            if ($statusCode >= 500 && ! ($e instanceof \Illuminate\Validation\ValidationException)) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json([
                        'success' => false,
                        'code'    => 500,
                        'message' => 'Terjadi kesalahan pada server.',
                    ], 500);
                }

                return $redirectToDashboard('error', 'Terjadi kendala pada sistem. Anda telah diarahkan kembali ke dashboard.');
            }
        });
    })->create();
