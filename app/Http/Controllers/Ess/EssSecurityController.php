<?php

namespace App\Http\Controllers\Ess;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ess\Concerns\ResolvesEmployee;
use App\Support\Ess\EssAuditService;
use App\Services\Security\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class EssSecurityController extends Controller
{
    use ResolvesEmployee;

    public function __construct(
        protected UserSessionService $userSessionService,
    ) {}

    public function updatePassword(Request $request, EssAuditService $audit): RedirectResponse
    {
        $user = $this->essUser();

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => $validated['password'],
        ]);

        $audit->logPasswordChanged($user);

        return redirect()
            ->route('ess.dashboard', ['tab' => 'security'])
            ->with('status', __('Password updated successfully.'));
    }

    public function destroyOthers(Request $request, EssAuditService $audit): RedirectResponse
    {
        $user = $this->essUser();

        $count = $this->userSessionService->terminateAllForUser(
            $user,
            $user,
            $request->session()->getId(),
            __('ESS logout other devices'),
        );

        $audit->logSessionsTerminated($user, $count, 'logout_others');

        return redirect()
            ->route('ess.dashboard', ['tab' => 'security'])
            ->with('status', __('Logged out :count other device(s).', ['count' => $count]));
    }
}
