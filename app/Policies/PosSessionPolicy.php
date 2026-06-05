<?php

namespace App\Policies;

use App\Models\Pos\PosSession;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class PosSessionPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('commercial.pos.sessions.view') || $user->can('pos.sessions.view');
    }

    public function view(User $user, PosSession $session): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->can('commercial.pos.sessions.admin')) {
            return $this->sameCompany($user, $session);
        }

        return $this->sameTenant($user, $session);
    }

    public function open(User $user): bool
    {
        return $user->can('commercial.pos.sessions.open') || $user->can('pos.sessions.open');
    }

    public function close(User $user, PosSession $session): bool
    {
        if (! $user->can('commercial.pos.sessions.close') && ! $user->can('pos.sessions.close')) {
            return false;
        }

        if ($user->can('commercial.pos.sessions.admin')) {
            return $this->sameCompany($user, $session);
        }

        return $this->sameTenant($user, $session);
    }

    public function approveVariance(User $user, PosSession $session): bool
    {
        if (! $user->can('commercial.pos.sessions.audit')
            && ! $user->can('pos.sessions.approve_variance')) {
            return false;
        }

        return $this->view($user, $session);
    }

    public function export(User $user, PosSession $session): bool
    {
        if (! $user->can('commercial.pos.sessions.audit')
            && ! $user->can('pos.sessions.export')) {
            return false;
        }

        return $this->view($user, $session);
    }

    public function audit(User $user, PosSession $session): bool
    {
        return ($user->can('commercial.pos.sessions.audit') || $user->can('pos.sessions.export'))
            && $this->view($user, $session);
    }

    protected function sameCompany(User $user, PosSession $session): bool
    {
        return (int) $user->company_id === (int) $session->company_id;
    }
}
