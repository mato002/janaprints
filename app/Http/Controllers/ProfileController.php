<?php

namespace App\Http\Controllers;

use App\Enums\UserSessionStatus;
use App\Http\Requests\ProfileUpdateRequest;
use App\Support\Branding\BrandingAssets;
use App\Services\Security\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected BrandingAssets $assets,
        protected UserSessionService $userSessionService,
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['company', 'defaultBranch', 'employee', 'roles']);

        $sessions = $this->userSessionService->sessionsForUser($user);
        $activeSessions = $sessions->where('status', UserSessionStatus::Active)->values();
        $permissions = $user->getAllPermissions()->pluck('name')->sort()->values();

        return view('profile.edit', [
            'user' => $user,
            'avatarUrl' => $this->assets->url($user->avatar_path),
            'roles' => $user->getRoleNames()->sort()->values(),
            'permissions' => $permissions,
            'permissionsByModule' => $permissions->groupBy(
                fn (string $permission) => explode('.', $permission)[0] ?: 'other',
            ),
            'activeSessions' => $activeSessions,
            'currentSessionId' => $request->session()->getId(),
            'activeSessionCount' => $activeSessions->count(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->boolean('remove_avatar')) {
            $this->assets->delete($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            $user->avatar_path = $this->assets->storeUserAvatar($user, $request->file('avatar'));
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->assets->delete($user->avatar_path);
        $user->update(['avatar_path' => null]);

        return Redirect::route('profile.edit')->with('status', 'avatar-removed');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $loginRoute = $request->session()->get('auth_context') === 'client'
            ? 'client.login'
            : 'admin.login';

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route($loginRoute);
    }
}
