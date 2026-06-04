<?php

namespace App\Policies;

use App\Models\Communications\SmsCampaign;
use App\Models\User;

class SmsCampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communications.sms.view');
    }

    public function view(User $user, SmsCampaign $campaign): bool
    {
        return $user->can('communications.sms.view') && $this->sameCompany($user, $campaign->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('communications.sms.send');
    }

    public function update(User $user, SmsCampaign $campaign): bool
    {
        return $user->can('communications.sms.send') && $this->sameCompany($user, $campaign->company_id);
    }

    public function approve(User $user, SmsCampaign $campaign): bool
    {
        return $user->can('communications.sms.approve') && $this->sameCompany($user, $campaign->company_id);
    }

    public function send(User $user, SmsCampaign $campaign): bool
    {
        return $user->can('communications.sms.send') && $this->sameCompany($user, $campaign->company_id);
    }

    public function audit(User $user): bool
    {
        return $user->can('communications.sms.audit');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }
}
