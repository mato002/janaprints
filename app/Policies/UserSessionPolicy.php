<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSessionRecord;

class UserSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('security.sessions.view');
    }

    public function view(User $user, UserSessionRecord $session): bool
    {
        return $user->can('security.sessions.view');
    }

    public function terminate(User $user, UserSessionRecord $session): bool
    {
        return $user->can('security.sessions.terminate');
    }

    public function forceLogout(User $user, User $target): bool
    {
        return $user->can('security.sessions.force_logout')
            && $user->getKey() !== $target->getKey();
    }

    public function audit(User $user): bool
    {
        return $user->can('security.sessions.audit');
    }
}
