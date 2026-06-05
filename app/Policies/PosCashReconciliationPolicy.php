<?php

namespace App\Policies;

use App\Models\Pos\PosCashReconciliation;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class PosCashReconciliationPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('commercial.pos.reconciliation.view');
    }

    public function view(User $user, PosCashReconciliation $reconciliation): bool
    {
        if (! $user->can('commercial.pos.reconciliation.view')) {
            return false;
        }

        if ($user->can('commercial.pos.sessions.admin')) {
            return (int) $user->company_id === (int) $reconciliation->company_id;
        }

        return $this->sameTenant($user, $reconciliation);
    }

    public function submit(User $user, PosCashReconciliation $reconciliation): bool
    {
        return $user->can('commercial.pos.reconciliation.create') && $this->view($user, $reconciliation);
    }

    public function review(User $user, PosCashReconciliation $reconciliation): bool
    {
        return $user->can('commercial.pos.reconciliation.approve') && $this->view($user, $reconciliation);
    }

    public function approve(User $user, PosCashReconciliation $reconciliation): bool
    {
        return $user->can('commercial.pos.reconciliation.approve') && $this->view($user, $reconciliation);
    }

    public function reject(User $user, PosCashReconciliation $reconciliation): bool
    {
        return $user->can('commercial.pos.reconciliation.approve') && $this->view($user, $reconciliation);
    }

    public function audit(User $user, PosCashReconciliation $reconciliation): bool
    {
        return $user->can('commercial.pos.reconciliation.audit') && $this->view($user, $reconciliation);
    }
}
