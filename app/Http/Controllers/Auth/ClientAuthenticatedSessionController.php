<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientAuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.client-login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('auth_context', 'client');

        return redirect()->intended('/');
    }
}
