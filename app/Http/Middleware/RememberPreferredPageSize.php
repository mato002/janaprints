<?php

namespace App\Http\Middleware;

use App\Support\Pagination\PreferredPageSize;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RememberPreferredPageSize
{
    public function handle(Request $request, Closure $next): Response
    {
        $size = PreferredPageSize::sanitize($request->input('per_page'));

        if ($size !== null) {
            PreferredPageSize::remember($size);
        }

        return $next($request);
    }
}
