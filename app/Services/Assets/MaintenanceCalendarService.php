<?php

namespace App\Services\Assets;

use App\Models\Assets\MaintenancePlan;
use App\Models\Assets\MaintenanceWorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MaintenanceCalendarService
{
    public function __construct(
        protected MaintenancePlanService $plans,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId, string $view = 'month', ?string $date = null): array
    {
        $anchor = $date ? Carbon::parse($date) : now();

        return match ($view) {
            'week' => $this->weekView($companyId, $branchId, $anchor),
            'upcoming' => $this->upcomingView($companyId, $branchId),
            'overdue' => $this->overdueView($companyId, $branchId),
            default => $this->monthView($companyId, $branchId, $anchor),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function monthView(int $companyId, ?int $branchId, Carbon $anchor): array
    {
        $start = $anchor->copy()->startOfMonth();
        $end = $anchor->copy()->endOfMonth();

        return [
            'view' => 'month',
            'period_label' => $anchor->format('F Y'),
            'entries' => $this->entriesBetween($companyId, $branchId, $start, $end),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function weekView(int $companyId, ?int $branchId, Carbon $anchor): array
    {
        $start = $anchor->copy()->startOfWeek();
        $end = $anchor->copy()->endOfWeek();

        return [
            'view' => 'week',
            'period_label' => $start->format('M j').' – '.$end->format('M j, Y'),
            'entries' => $this->entriesBetween($companyId, $branchId, $start, $end),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function upcomingView(int $companyId, ?int $branchId): array
    {
        return [
            'view' => 'upcoming',
            'period_label' => __('Next 30 days'),
            'entries' => $this->plans->upcomingSchedules($companyId, $branchId)
                ->merge($this->scheduledWorkOrders($companyId, $branchId, now(), now()->addDays(30))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function overdueView(int $companyId, ?int $branchId): array
    {
        $overduePlans = $this->plans->overdue($companyId, $branchId)->map(fn ($plan) => [
            'type' => 'plan',
            'asset_name' => $plan->asset?->asset_name,
            'label' => $plan->plan_name,
            'due_date' => $plan->next_due_date?->format('Y-m-d'),
            'status' => __('Overdue'),
            'priority' => __('Normal'),
            'branch_id' => $plan->branch_id,
        ]);

        return [
            'view' => 'overdue',
            'period_label' => __('Overdue maintenance'),
            'entries' => $overduePlans,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function entriesBetween(int $companyId, ?int $branchId, Carbon $start, Carbon $end): Collection
    {
        $workOrders = $this->scheduledWorkOrders($companyId, $branchId, $start, $end);
        $planEntries = MaintenancePlan::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('next_due_date', [$start->toDateString(), $end->toDateString()])
            ->with(['asset:id,asset_name'])
            ->get()
            ->map(fn (MaintenancePlan $plan) => [
                'type' => 'plan',
                'asset_name' => $plan->asset?->asset_name,
                'label' => $plan->plan_name,
                'due_date' => $plan->next_due_date?->format('Y-m-d'),
                'status' => __('Scheduled'),
                'priority' => __('Normal'),
                'branch_id' => $plan->branch_id,
            ]);

        return $workOrders->merge($planEntries)->sortBy('due_date')->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function scheduledWorkOrders(int $companyId, ?int $branchId, Carbon $start, Carbon $end): Collection
    {
        return MaintenanceWorkOrder::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('scheduled_for')
            ->whereBetween('scheduled_for', [$start, $end])
            ->with(['asset:id,asset_name', 'branch:id,name'])
            ->get()
            ->map(fn (MaintenanceWorkOrder $order) => [
                'type' => 'work_order',
                'asset_name' => $order->asset?->asset_name,
                'label' => $order->work_order_no,
                'due_date' => $order->scheduled_for?->format('Y-m-d'),
                'status' => $order->status->label(),
                'priority' => $order->priority->label(),
                'branch_id' => $order->branch_id,
                'url' => route('admin.assets.maintenance.work-orders.show', $order),
            ]);
    }
}
