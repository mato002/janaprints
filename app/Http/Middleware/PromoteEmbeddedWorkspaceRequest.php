<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PromoteEmbeddedWorkspaceRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin') && ! $request->is('admin/*')) {
            return $next($request);
        }

        if (
            $request->isMethod('GET')
            && $request->query('embedded') === '1'
            && ! $request->headers->has('Turbo-Frame')
        ) {
            $query = $request->query();
            unset($query['embedded']);

            $url = $request->url();

            if ($query !== []) {
                $url .= '?'.http_build_query($query);
            }

            return redirect()->to($url);
        }

        return $next($request);
    }
}
