<?php

namespace App\Policies;

use App\Governance\WorkflowRulesCenter;
use App\Models\Governance\WorkflowRule;
use App\Models\User;

class WorkflowRulesPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('governance.workflow.view');
    }

    public function view(User $user, WorkflowRule $rule): bool
    {
        return $user->can('governance.workflow.view');
    }

    public function create(User $user): bool
    {
        return $user->can('governance.workflow.create');
    }

    public function update(User $user, WorkflowRule $rule): bool
    {
        return $user->can('governance.workflow.manage');
    }

    public function activate(User $user, WorkflowRule $rule): bool
    {
        return $user->can('governance.workflow.manage');
    }

    public function manage(User $user): bool
    {
        return $user->can('governance.workflow.manage');
    }
}
