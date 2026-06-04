<?php

namespace App\Policies;

use App\Models\Communications\ErpNotification;
use App\Models\User;

class ErpNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communications.notifications.view');
    }

    public function view(User $user, ErpNotification $notification): bool
    {
        if (! $user->can('communications.notifications.view')) {
            return false;
        }

        if ($user->can('communications.notifications.admin')) {
            return $this->sameCompany($user, $notification->company_id);
        }

        return $notification->recipient_user_id === $user->id
            && $this->sameCompany($user, $notification->company_id);
    }

    public function manage(User $user, ErpNotification $notification): bool
    {
        return $user->can('communications.notifications.manage')
            && $notification->recipient_user_id === $user->id
            && $this->sameCompany($user, $notification->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('communications.notifications.manage')
            || $user->can('communications.notifications.admin');
    }

    public function admin(User $user): bool
    {
        return $user->can('communications.notifications.admin');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }
}
