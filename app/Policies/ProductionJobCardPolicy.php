<?php

namespace App\Policies;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class ProductionJobCardPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('production.view');
    }

    public function viewSchedulingWorkspace(User $user): bool
    {
        return $user->can('production.scheduling.view');
    }

    public function view(User $user, ProductionJobCard $jobCard): bool
    {
        if (! $this->sameTenant($user, $jobCard)) {
            return false;
        }

        if ($user->can('production.view')) {
            return true;
        }

        return $user->can('sales_orders.production')
            && $jobCard->sales_order_id !== null;
    }

    public function create(User $user): bool
    {
        return $user->can('production.create');
    }

    public function update(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.edit')
            && $this->sameTenant($user, $jobCard)
            && $jobCard->status->isEditable();
    }

    public function delete(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.delete')
            && $this->sameTenant($user, $jobCard)
            && $jobCard->status === ProductionJobCardStatus::Draft;
    }

    public function schedule(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.schedule')
            && $this->sameTenant($user, $jobCard)
            && in_array($jobCard->status, [
                ProductionJobCardStatus::Draft,
                ProductionJobCardStatus::Queued,
                ProductionJobCardStatus::OnHold,
            ], true);
    }

    public function start(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.start')
            && $this->sameTenant($user, $jobCard)
            && in_array($jobCard->status, [
                ProductionJobCardStatus::Queued,
                ProductionJobCardStatus::Rework,
            ], true);
    }

    public function complete(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.complete')
            && $this->sameTenant($user, $jobCard)
            && in_array($jobCard->status, [
                ProductionJobCardStatus::InProduction,
                ProductionJobCardStatus::QualityCheck,
                ProductionJobCardStatus::Completed,
            ], true);
    }

    public function fulfil(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.complete')
            && $this->sameTenant($user, $jobCard)
            && $jobCard->status === ProductionJobCardStatus::ReadyForDispatch;
    }

    public function qc(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.qc')
            && $this->sameTenant($user, $jobCard)
            && $jobCard->status === ProductionJobCardStatus::QualityCheck;
    }

    public function transition(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.edit') && $this->sameTenant($user, $jobCard);
    }

    public function approveCustomerHold(User $user, ProductionJobCard $jobCard): bool
    {
        return $user->can('production.qc')
            && $this->sameTenant($user, $jobCard)
            && $jobCard->status === ProductionJobCardStatus::AwaitingCustomerApproval;
    }
}
