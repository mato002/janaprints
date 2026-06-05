<?php

namespace App\Listeners;

use App\Enums\LoginAttemptFailureReason;
use App\Services\Security\LoginAttemptService;
use App\Services\Security\UserSessionService;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogAuthenticationActivity
{
    public function __construct(
        protected UserSessionService $userSessionService,
        protected LoginAttemptService $loginAttemptService,
        protected Request $request,
    ) {}

    public function handleLogin(Login $event): void
    {
        ActivityLogger::log('login', $event->user, $event->user->getKey());
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            ActivityLogger::log('logout', $event->user, $event->user->getKey());
        }

        $this->userSessionService->recordLogout($this->request->session()->getId());
    }

    public function handleFailed(Failed $event): void
    {
        $email = (string) ($event->credentials['email'] ?? '');

        if ($email === '') {
            return;
        }

        $user = $event->user instanceof \App\Models\User
            ? $event->user
            : \App\Models\User::query()->where('email', $email)->first();

        if ($user && ! $user->is_active) {
            return;
        }

        $this->loginAttemptService->record(
            $email,
            LoginAttemptFailureReason::InvalidCredentials,
            $user,
        );
    }

    public function handleLockout(Lockout $event): void
    {
        $email = (string) $event->request->input('email', '');

        if ($email === '') {
            return;
        }

        $user = \App\Models\User::query()->where('email', $email)->first();

        $this->loginAttemptService->record(
            $email,
            LoginAttemptFailureReason::LockedOut,
            $user,
            $event->request,
        );
    }
}
