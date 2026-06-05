<?php

namespace App\Policies;

use App\Models\Hr\PerformanceReview;
use App\Models\User;

class PerformanceReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.performance.view');
    }

    public function view(User $user, PerformanceReview $review): bool
    {
        return $user->can('hr.performance.view') && $this->sameCompany($user, $review->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.performance.manage');
    }

    public function update(User $user, PerformanceReview $review): bool
    {
        return $user->can('hr.performance.manage') && $this->sameCompany($user, $review->company_id);
    }

    public function delete(User $user, PerformanceReview $review): bool
    {
        return $user->can('hr.performance.manage') && $this->sameCompany($user, $review->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}
