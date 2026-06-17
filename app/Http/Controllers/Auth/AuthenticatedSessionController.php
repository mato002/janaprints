<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Services\Security\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected UserSessionService $userSessionService,
    ) {}
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(AdminLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        if ($user && $user->prefersEssPortal()) {
            $request->session()->put('auth_context', 'ess');
            $this->userSessionService->recordLogin($user, $request);

            return redirect()->intended(route('ess.dashboard', absolute: false));
        }

        $request->session()->put('auth_context', 'admin');

        $this->userSessionService->recordLogin($user, $request);

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $loginRoute = match ($request->session()->get('auth_context')) {
            'client' => 'client.login',
            'ess' => 'admin.login',
            default => 'admin.login',
        };

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route($loginRoute);
    }
}
