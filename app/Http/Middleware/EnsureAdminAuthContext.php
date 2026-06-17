<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->session()->get('auth_context') === 'client') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => __('Please sign in to the ERP.')]);
        }

        $user = $request->user();

        if ($user && $user->prefersEssPortal()) {
            return redirect()->route('ess.dashboard');
        }

        return $next($request);
    }
}
