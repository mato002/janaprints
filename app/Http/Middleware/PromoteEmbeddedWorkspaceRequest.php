<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy no-op — embedded workspace requests must never be redirected.
 *
 * Previously stripped ?embedded=1 and caused a second redirect into the module shell.
 * Kept as an explicit pass-through so middleware order stays stable if referenced elsewhere.
 */
class PromoteEmbeddedWorkspaceRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

