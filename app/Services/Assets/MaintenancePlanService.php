<?php

namespace App\Services\Assets;

use App\Enums\MaintenanceFrequencyType;
use App\Models\Assets\MaintenancePlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MaintenancePlanService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $companyId, ?int $branchId): MaintenancePlan
    {
        $nextDue = $data['next_due_date'] ?? $this->calculateNextDueDate(
            MaintenanceFrequencyType::from($data['frequency_type']),
            (int) ($data['frequency_value'] ?? 1),
        );

        return MaintenancePlan::query()->create([
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? $branchId,
            'fixed_asset_id' => $data['fixed_asset_id'],
            'plan_name' => $data['plan_name'],
            'frequency_type' => $data['frequency_type'],
            'frequency_value' => $data['frequency_value'] ?? 1,
            'next_due_date' => $nextDue,
            'is_active' => $data['is_active'] ?? true,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * @return Collection<int, MaintenancePlan>
     */
    public function overdue(int $companyId, ?int $branchId = null): Collection
    {
        return MaintenancePlan::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('next_due_date')
            ->where('next_due_date', '<', now()->toDateString())
            ->with(['asset:id,asset_name,asset_number'])
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function upcomingSchedules(int $companyId, ?int $branchId = null, int $days = 30): Collection
    {
        $until = now()->addDays($days)->toDateString();

        return MaintenancePlan::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('next_due_date')
            ->whereBetween('next_due_date', [now()->toDateString(), $until])
            ->with(['asset:id,asset_name,asset_number,branch_id', 'asset.branch:id,name'])
            ->orderBy('next_due_date')
            ->get()
            ->map(fn (MaintenancePlan $plan) => [
                'plan' => $plan,
                'asset_name' => $plan->asset?->asset_name,
                'due_date' => $plan->next_due_date?->format('Y-m-d'),
                'is_overdue' => $plan->next_due_date?->isPast() ?? false,
                'readiness' => __('Schedule ready — work order generation available in a later phase.'),
            ]);
    }

    public function calculateNextDueDate(MaintenanceFrequencyType $type, int $value = 1, ?Carbon $from = null): Carbon
    {
        $from ??= now();

        return match ($type) {
            MaintenanceFrequencyType::Daily => $from->copy()->addDays($value),
            MaintenanceFrequencyType::Weekly => $from->copy()->addWeeks($value),
            MaintenanceFrequencyType::Monthly => $from->copy()->addMonths($value),
            MaintenanceFrequencyType::Quarterly => $from->copy()->addMonths(3 * $value),
            MaintenanceFrequencyType::SemiAnnual => $from->copy()->addMonths(6 * $value),
            MaintenanceFrequencyType::Annual => $from->copy()->addYears($value),
            MaintenanceFrequencyType::MeterBased => $from->copy()->addMonths($value),
        };
    }
}
