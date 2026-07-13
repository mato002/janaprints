<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Legacy pass-through kept for middleware order stability.
 *
 * Full-page ?embedded=1 refreshes are restored to the module desk by
 * RedirectToModuleWorkspaceShell. Turbo-frame fetches keep bare content.
 */
class PromoteEmbeddedWorkspaceRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

