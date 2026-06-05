<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSessionRecord;
use App\Services\Security\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserSessionController extends Controller
{
    public function __construct(
        protected UserSessionService $userSessionService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', UserSessionRecord::class);

        $status = $request->string('status')->toString() ?: 'all';
        $search = $request->string('search')->toString() ?: null;

        return view('admin.user-sessions.index', [
            'sessions' => $this->userSessionService->paginate(
                $status !== 'all' ? $status : null,
                $search,
            ),
            'metrics' => $this->userSessionService->dashboardMetrics(),
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function show(UserSessionRecord $userSession): View
    {
        $this->authorize('view', $userSession);

        $userSession->load(['user', 'company', 'branch', 'revokedByUser']);

        return view('admin.user-sessions.show', [
            'session' => $userSession,
        ]);
    }

    public function terminate(Request $request, UserSessionRecord $userSession): RedirectResponse
    {
        $this->authorize('terminate', $userSession);

        $this->userSessionService->terminate(
            $userSession,
            $request->user(),
            $request->string('reason')->toString() ?: null,
        );

        return back()->with('status', __('Session terminated.'));
    }

    public function terminateAll(Request $request, User $user): RedirectResponse
    {
        $this->authorize('forceLogout', $user);

        $count = $this->userSessionService->terminateAllForUser(
            $user,
            $request->user(),
            $request->session()->getId(),
            $request->string('reason')->toString() ?: __('Terminate all sessions'),
        );

        return back()->with('status', __(':count session(s) terminated.', ['count' => $count]));
    }

    public function forceLogout(Request $request, User $user): RedirectResponse
    {
        $this->authorize('forceLogout', $user);

        $count = $this->userSessionService->forceLogoutUser(
            $user,
            $request->user(),
            $request->string('reason')->toString() ?: __('Force logout'),
        );

        return back()->with('status', __('User logged out from :count session(s).', ['count' => $count]));
    }
}
