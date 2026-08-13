<?php

namespace App\Policies;

use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class QualityCheckPolicy
{
    use ChecksCrmTenant;

    public function viewWorkspace(User $user): bool
    {
        return $user->can('production.quality.view');
    }

    public function viewAny(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.view') && $this->sameTenant($user, $jobCard);
    }

    public function create(User $user, ProductionJobCard $jobCard): bool
    {
        if (! $user->can('production.qc') || ! $this->sameTenant($user, $jobCard)) {
            return false;
        }

        // Normal path: job is waiting in QC.
        if ($jobCard->status === ProductionJobCardStatus::QualityCheck) {
            return true;
        }

        // Late / catch-up path: job advanced without a pass, but readiness still blocks on QC.
        if (in_array($jobCard->status, [
            ProductionJobCardStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch,
        ], true)) {
            return ! $this->hasPassedQc($jobCard);
        }

        return false;
    }

    public function approveCustomerHold(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.qc')
            && $this->sameTenant($user, $jobCard)
            && $jobCard->status === ProductionJobCardStatus::AwaitingCustomerApproval;
    }

    public function view(User $user, QualityCheck $check): bool
    {
        return $user->can('production.view') && $this->sameTenant($user, $check->jobCard);
    }

    protected function hasPassedQc(ProductionJobCard $jobCard): bool
    {
        return QualityCheck::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('result', QualityCheckResult::Passed)
            ->exists();
    }
}
