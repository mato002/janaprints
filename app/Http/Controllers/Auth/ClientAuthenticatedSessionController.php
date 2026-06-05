<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ClientLoginRequest;
use App\Services\Security\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientAuthenticatedSessionController extends Controller
{
    public function __construct(
        protected UserSessionService $userSessionService,
    ) {}
    public function create(): View
    {
        return view('auth.client-login');
    }

    public function store(ClientLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('auth_context', 'client');

        $this->userSessionService->recordLogin($request->user(), $request);

        return redirect()->intended(route('client.dashboard', absolute: false));
    }
}
