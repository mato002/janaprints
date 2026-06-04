<?php

namespace App\Policies;

use App\Models\Communications\CommunicationLog;
use App\Models\User;

class CommunicationLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communications.logs.view');
    }

    public function view(User $user, CommunicationLog $log): bool
    {
        return $user->can('communications.logs.view') && $this->sameCompany($user, $log->company_id);
    }

    public function audit(User $user): bool
    {
        return $user->can('communications.logs.audit');
    }

    public function export(User $user): bool
    {
        return $user->can('communications.logs.export');
    }

    public function admin(User $user): bool
    {
        return $user->can('communications.logs.admin');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }
}
