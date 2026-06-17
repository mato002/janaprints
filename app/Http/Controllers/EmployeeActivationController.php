<?php

namespace App\Http\Controllers;

use App\Services\EmailIdentity\EmployeeActivationService;
use App\Services\Security\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class EmployeeActivationController extends Controller
{
    public function __construct(
        protected EmployeeActivationService $activationService,
        protected UserSessionService $userSessionService,
    ) {}

    public function show(string $token): View|RedirectResponse
    {
        $activation = $this->activationService->findPendingActivation($token);

        if (! $activation) {
            return view('auth.employee-activation-expired');
        }

        if (! app(\App\Support\Hr\EmployeeAccessGovernanceService::class)->canCompleteActivation($activation->employee)) {
            return view('auth.employee-activation-expired');
        }

        return view('auth.employee-activate', [
            'token' => $token,
            'employeeName' => $activation->employee->full_name,
            'loginEmail' => $activation->personal_email,
            'expiresAt' => $activation->expires_at,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $activation = $this->activationService->findPendingActivation($token);

        if (! $activation) {
            return redirect()
                ->route('employee.activate.show', ['token' => $token])
                ->withErrors(['token' => __('This activation link is invalid or has expired.')]);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $this->activationService->activate($activation, $validated['password']);

        try {
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->put('auth_context', $user->prefersEssPortal() ? 'ess' : 'admin');
            $this->userSessionService->recordLogin($user, $request);

            $dashboardRoute = $user->prefersEssPortal() ? 'ess.dashboard' : 'admin.dashboard';

            return redirect()
                ->route($dashboardRoute)
                ->with('status', __('Your account is activated. Welcome to JanaPrints.'));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.login')
                ->with('status', __('Your account is activated. Sign in with your email and new password.'));
        }
    }
}
