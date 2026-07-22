<?php

namespace App\Services\Assets;

use App\Models\Assets\AssetDowntimeRecord;
use App\Models\Assets\MaintenancePlan;
use App\Models\Assets\MaintenanceTechnician;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceWorkspaceService
{
    public function __construct(
        protected MaintenanceDashboardService $dashboard,
        protected MaintenanceWorkOrderIndexService $workOrders,
        protected MaintenancePlanService $plans,
        protected MaintenanceCalendarService $calendar,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $user = $request->user();
        $activeTab = $this->resolveTab($request, $user);
        $payload = [
            'activeTab' => $activeTab,
            'tabs' => $this->tabs($user),
            'hubUrl' => route('admin.assets.maintenance.dashboard'),
        ];

        return match ($activeTab) {
            'work-orders' => array_merge($payload, $this->workOrders->build($request)),
            'plans' => array_merge($payload, $this->plansTab($request)),
            'calendar' => array_merge($payload, $this->calendarTab($request)),
            'downtime' => array_merge($payload, $this->downtimeTab($request)),
            'technicians' => array_merge($payload, $this->techniciansTab($request)),
            default => array_merge($payload, [
                'stats' => $this->dashboard->build((int) tenant()->companyId(), tenant()->branchId()),
            ]),
        };
    }

    public function resolveTab(Request $request, ?User $user = null): string
    {
        $user ??= $request->user();
        $tab = (string) $request->query('tab', 'overview');

        if (in_array($tab, ['maintenance', 'maintenance-dashboard'], true)) {
            $tab = 'overview';
        }

        $allowed = array_keys($this->tabs($user));

        return in_array($tab, $allowed, true) ? $tab : 'overview';
    }

    /**
     * @return array<string, string>
     */
    public function tabs(?User $user): array
    {
        $tabs = [
            'overview' => __('Overview'),
            'work-orders' => __('Work Orders'),
            'plans' => __('Plans'),
        ];

        if ($user?->can('maintenance.calendar.view')) {
            $tabs['calendar'] = __('Calendar');
        }

        $tabs['downtime'] = __('Downtime');
        $tabs['technicians'] = __('Technicians');

        return $tabs;
    }

    /**
     * @return array<string, mixed>
     */
    protected function plansTab(Request $request): array
    {
        $plans = MaintenancePlan::query()
            ->forTenant()
            ->with(['asset:id,asset_name,asset_number'])
            ->when($request->query('active'), fn ($q, $v) => $q->where('is_active', $v === '1'))
            ->orderBy('next_due_date')
            ->paginate(config('platform.pagination.default', 15))
            ->withQueryString();

        return [
            'plans' => $plans,
            'upcoming' => $this->plans->upcomingSchedules((int) tenant()->companyId(), tenant()->branchId()),
            'overdue' => $this->plans->overdue((int) tenant()->companyId(), tenant()->branchId()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function calendarTab(Request $request): array
    {
        $view = $request->query('view', 'month');

        return [
            'calendar' => $this->calendar->build(
                (int) tenant()->companyId(),
                tenant()->branchId(),
                in_array($view, ['month', 'week', 'upcoming', 'overdue'], true) ? $view : 'month',
                $request->query('date'),
            ),
            'activeView' => $view,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function downtimeTab(Request $request): array
    {
        return [
            'records' => AssetDowntimeRecord::query()
                ->forTenant()
                ->with(['asset:id,asset_name,asset_number', 'workOrder:id,work_order_no'])
                ->latest('start_time')
                ->paginate(config('platform.pagination.default', 15))
                ->withQueryString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function techniciansTab(Request $request): array
    {
        return [
            'technicians' => MaintenanceTechnician::query()
                ->forTenant()
                ->with(['vendor:id,vendor_name', 'user:id,name'])
                ->withCount('assignedWorkOrders')
                ->orderBy('name')
                ->paginate(config('platform.pagination.default', 15))
                ->withQueryString(),
        ];
    }
}
