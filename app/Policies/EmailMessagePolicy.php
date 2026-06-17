<?php

namespace App\Policies;

use App\Enums\EmailDeliveryStatus;
use App\Models\Communications\EmailMessage;
use App\Models\User;

class EmailMessagePolicy
{
    public function view(User $user, EmailMessage $message): bool
    {
        return $user->can('communications.email.view') && $this->sameCompany($user, $message->company_id);
    }

    public function cancel(User $user, EmailMessage $message): bool
    {
        return $user->can('communications.email.send')
            && $this->sameCompany($user, $message->company_id)
            && $message->status === EmailDeliveryStatus::Queued;
    }

    public function retry(User $user, EmailMessage $message): bool
    {
        return $user->can('communications.email.send')
            && $this->sameCompany($user, $message->company_id)
            && in_array($message->status, [EmailDeliveryStatus::Failed, EmailDeliveryStatus::Bounced], true);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }
}
