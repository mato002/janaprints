<?php

namespace App\Policies;

use App\Models\Communications\EmailCampaign;
use App\Models\User;

class EmailCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communications.email.view');
    }

    public function view(User $user, EmailCampaign $campaign): bool
    {
        return $user->can('communications.email.view') && $this->sameCompany($user, $campaign->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('communications.email.send');
    }

    public function send(User $user, EmailCampaign $campaign): bool
    {
        return $user->can('communications.email.send') && $this->sameCompany($user, $campaign->company_id);
    }

    public function schedule(User $user, EmailCampaign $campaign): bool
    {
        return $user->can('communications.email.schedule') && $this->sameCompany($user, $campaign->company_id);
    }

    public function manage(User $user): bool
    {
        return $user->can('communications.email.manage');
    }

    public function audit(User $user): bool
    {
        return $user->can('communications.email.audit');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }
}
