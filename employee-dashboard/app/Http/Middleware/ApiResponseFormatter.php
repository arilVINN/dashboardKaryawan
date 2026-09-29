<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseFormatter
{
    public function handle($request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        // Jika sudah dalam format standar, biarkan apa adanya
        if ($response instanceof JsonResponse && $response->original && array_key_exists('success', $response->original)) {
            return $response;
        }

        $payload = [
            'success' => $response->isSuccessful(),
            'code'    => $response->getStatusCode(),
            // Gunakan teks standar bila belum ada custom message
            'message' => $response->getStatusCode() === 200
                         ? 'OK'
                         : ($response->exception ? $response->exception->getMessage() : Response::$statusTexts[$response->getStatusCode()] ?? 'Error'),
            'data'    => $response->getOriginalContent() ?: null,
        ];

        return response()->json($payload, $response->getStatusCode());
    }
}