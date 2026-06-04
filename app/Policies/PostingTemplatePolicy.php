<?php

namespace App\Policies;

use App\Models\Accounting\PostingTemplate;
use App\Models\User;

class PostingTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.posting.view');
    }

    public function view(User $user, PostingTemplate $template): bool
    {
        return $user->can('accounting.posting.view') && $this->sameCompany($user, $template->company_id);
    }

    public function manage(User $user): bool
    {
        return $user->can('accounting.posting.manage');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}
