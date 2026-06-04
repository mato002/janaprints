<?php

namespace App\Policies;

use App\Models\Accounting\Journal;
use App\Models\User;

class JournalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('accounting.journals.view');
    }

    public function view(User $user, Journal $journal): bool
    {
        return $user->can('accounting.journals.view') && $this->sameCompany($user, $journal->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('accounting.journals.create');
    }

    public function update(User $user, Journal $journal): bool
    {
        return $user->can('accounting.journals.create')
            && $this->sameCompany($user, $journal->company_id)
            && $journal->status->isEditable();
    }

    public function delete(User $user, Journal $journal): bool
    {
        return $user->can('accounting.journals.create')
            && $this->sameCompany($user, $journal->company_id)
            && $journal->status->isEditable();
    }

    public function post(User $user, Journal $journal): bool
    {
        return $user->can('accounting.journals.post') && $this->sameCompany($user, $journal->company_id);
    }

    public function reverse(User $user, Journal $journal): bool
    {
        return $user->can('accounting.journals.reverse') && $this->sameCompany($user, $journal->company_id);
    }

    public function viewReports(User $user): bool
    {
        return $user->can('accounting.reports.view');
    }

    public function viewDashboard(User $user): bool
    {
        return $user->can('accounting.dashboard.view');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}
