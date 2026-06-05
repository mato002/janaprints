<?php

namespace App\Http\Controllers;

use App\Models\UserSessionRecord;
use App\Services\Security\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MySessionsController extends Controller
{
    public function __construct(
        protected UserSessionService $userSessionService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        return view('profile.sessions', [
            'sessions' => $this->userSessionService->sessionsForUser($user),
            'currentSessionId' => $currentSessionId,
        ]);
    }

    public function destroyOthers(Request $request): RedirectResponse
    {
        $count = $this->userSessionService->terminateAllForUser(
            $request->user(),
            $request->user(),
            $request->session()->getId(),
            __('Logout other devices'),
        );

        return back()->with('status', __('Logged out :count other device(s).', ['count' => $count]));
    }

    public function destroy(Request $request, UserSessionRecord $userSession): RedirectResponse
    {
        if ($userSession->user_id !== $request->user()->getKey()) {
            abort(403);
        }

        if ($userSession->isCurrentSession($request->session()->getId())) {
            abort(403, __('Cannot terminate your current session from this screen.'));
        }

        $this->userSessionService->terminate($userSession, $request->user(), __('Self-service logout'));

        return back()->with('status', __('Session terminated.'));
    }
}
