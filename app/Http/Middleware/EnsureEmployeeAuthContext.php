<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeAuthContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('admin.login');
        }

        if ($user->isClientPortalAccount() || $user->employee_id === null || ! $user->can('ess.access')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => __('Please sign in with an employee account.')]);
        }

        if ($request->session()->get('auth_context') !== 'ess') {
            $request->session()->put('auth_context', 'ess');
        }

        return $next($request);
    }
}
