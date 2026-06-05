<?php

namespace App\Policies;

use App\Models\Governance\WorkflowEscalationRule;
use App\Models\User;

class WorkflowEscalationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('governance.escalations.view');
    }

    public function view(User $user, WorkflowEscalationRule $rule): bool
    {
        return $user->can('governance.escalations.view')
            && $this->sameCompany($user, $rule);
    }

    public function create(User $user): bool
    {
        return $user->can('governance.escalations.manage');
    }

    public function update(User $user, WorkflowEscalationRule $rule): bool
    {
        return $user->can('governance.escalations.manage')
            && $this->sameCompany($user, $rule);
    }

    public function activate(User $user, WorkflowEscalationRule $rule): bool
    {
        return $user->can('governance.escalations.manage')
            && $this->sameCompany($user, $rule);
    }

    public function deactivate(User $user, WorkflowEscalationRule $rule): bool
    {
        return $user->can('governance.escalations.manage')
            && $this->sameCompany($user, $rule);
    }

    protected function sameCompany(User $user, WorkflowEscalationRule $rule): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $rule->company_id;
    }
}
