<?php

namespace App\Http\Middleware;

use App\Support\Navigation\WorkspaceNavigationResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureWorkspaceNavigationQuery
{
    public function __construct(
        protected WorkspaceNavigationResolver $navigation,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $route = $request->route()?->getName();

        if ($request->isMethod('GET') && $route) {
            $query = $this->navigation->filterPreservedQuery($request->query());

            if ($query !== []) {
                session()->put("workspace_nav.query.{$route}", $query);
            }
        }

        return $response;
    }
}
