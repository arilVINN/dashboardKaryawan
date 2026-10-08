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
$middleware->validateCsrfTokens(except: [
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
                ], $statusCode);
            }
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json(['message' => 'CSRF token mismatch.'], 419);
                }
                return redirect()->route('login')->withErrors(['message' => 'Sesi telah kadaluarsa. Silakan login kembali.']);
            }
        });
    })->create();
