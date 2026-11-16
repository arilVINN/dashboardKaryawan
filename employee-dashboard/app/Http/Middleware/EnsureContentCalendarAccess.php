<?php

namespace App\Http\Middleware;

use App\Support\ContentCalendarAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureContentCalendarAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(ContentCalendarAccess::allows($request->user()), 403);

        return $next($request);
    }
}
