<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAuthContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->session()->get('auth_context') !== 'client') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('client.login')
                ->withErrors(['email' => __('Please sign in to the client portal.')]);
        }

        return $next($request);
    }
}
