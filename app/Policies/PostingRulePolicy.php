<?php

namespace App\Policies;

use App\Models\Accounting\PostingRule;
use App\Models\User;

class PostingRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.posting_rules.view')
            || $user->can('accounting.posting_rules.audit')
            || $user->can('accounting.posting.view');
    }

    public function view(User $user, PostingRule $rule): bool
    {
        return $this->viewAny($user) && $this->sameCompany($user, $rule->company_id);
    }

    public function manage(User $user): bool
    {
        return $user->can('accounting.posting_rules.manage')
            || $user->can('accounting.posting.manage');
    }

    public function audit(User $user): bool
    {
        return $user->can('accounting.posting_rules.audit')
            || $user->can('accounting.posting_rules.view')
            || $user->can('accounting.posting.view');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}
