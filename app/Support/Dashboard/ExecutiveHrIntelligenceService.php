<?php

namespace App\Support\Dashboard;

use App\Enums\PerformanceRating;
use App\Models\Hr\PerformanceReview;
use App\Models\User;
use App\Support\Hr\HrDashboardIntelligenceService;
use App\Support\Reports\HrReportQueries;
use App\Support\Reports\HrReportScope;
use App\Support\Reports\IntelligenceAggregateQueries;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class ExecutiveHrIntelligenceService
{
    public function __construct(
        protected HrDashboardIntelligenceService $hrIntelligence,
        protected HrReportQueries $reportQueries,
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        if (! $this->canView()) {
            return $this->emptyPayload();
        }

        $companyId = (int) (tenant()->companyId() ?? auth()->user()?->company_id);
        if (! $companyId) {
            return $this->emptyPayload();
        }

        $branchId = tenant()->branchId();
        $monthStart = now()->startOfMonth()->toDateString();
        $today = now()->toDateString();

        $snapshot = $this->hrIntelligence->snapshot($companyId);
        $kpiMap = collect($snapshot['kpis'] ?? [])->keyBy('key');

        $payrollMtd = $this->reportQueries->payrollCostSummary(new HrReportScope(
            $companyId,
            $branchId,
            $monthStart,
            $today,
        ));

        $employees = $this->metric($kpiMap, 'total_employees');
        $presentToday = $this->metric($kpiMap, 'present_today');
        $onLeave = $this->metric($kpiMap, 'on_leave');
        $attendancePercent = $this->metric($kpiMap, 'attendance_percent');
        $overtimeCost = $this->metric($kpiMap, 'overtime_cost');
        $contractExpiry = $this->metric($kpiMap, 'contract_expiry');
        $trainingDue = $this->metric($kpiMap, 'training_due');
        $performanceAlerts = $this->performanceAlerts($companyId, $branchId);

        $payrollDisplay = $payrollMtd['net'] > 0
            ? $this->queries->money($payrollMtd['net'])
            : '—';

        $available = $employees['raw'] !== null
            || $presentToday['raw'] !== null
            || $payrollMtd['net'] > 0;

        return [
            'visible' => true,
            'available' => $available,
            'source' => 'hr_intelligence',
            'employees' => $employees['display'],
            'employees_raw' => $employees['raw'],
            'present_today' => $presentToday['display'],
            'present_today_raw' => $presentToday['raw'],
            'attendance_percent' => $attendancePercent['display'],
            'attendance_percent_raw' => $attendancePercent['raw'],
            'on_leave' => $onLeave['display'],
            'on_leave_raw' => $onLeave['raw'],
            'payroll_cost_mtd' => $payrollDisplay,
            'payroll_cost_mtd_raw' => $payrollMtd['net'] > 0 ? $payrollMtd['net'] : null,
            'overtime_cost' => $overtimeCost['display'],
            'overtime_cost_raw' => $overtimeCost['raw'],
            'contract_expiry' => $contractExpiry['display'],
            'contract_expiry_raw' => $contractExpiry['raw'],
            'training_due' => $trainingDue['display'],
            'training_due_raw' => $trainingDue['raw'],
            'performance_alerts' => (string) $performanceAlerts,
            'performance_alerts_raw' => $performanceAlerts,
            'links' => $this->hrLinks(),
        ];
    }

    /**
     * Compact shape for legacy HR pulse partials.
     *
     * @return array<string, mixed>
     */
    public function pulse(): array
    {
        $snapshot = $this->build();

        if (! $snapshot['available']) {
            return [
                'present' => 0,
                'on_leave' => 0,
                'contract_expiry' => 0,
                'performance_alerts' => 0,
            ];
        }

        return [
            'present' => $snapshot['present_today_raw'] ?? 0,
            'on_leave' => $snapshot['on_leave_raw'] ?? 0,
            'contract_expiry' => $snapshot['contract_expiry_raw'] ?? 0,
            'performance_alerts' => $snapshot['performance_alerts_raw'] ?? 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyPayload(): array
    {
        return [
            'visible' => false,
            'available' => false,
            'source' => 'none',
            'employees' => '—',
            'employees_raw' => null,
            'present_today' => '—',
            'present_today_raw' => null,
            'attendance_percent' => '—',
            'attendance_percent_raw' => null,
            'on_leave' => '—',
            'on_leave_raw' => null,
            'payroll_cost_mtd' => '—',
            'payroll_cost_mtd_raw' => null,
            'overtime_cost' => '—',
            'overtime_cost_raw' => null,
            'contract_expiry' => '—',
            'contract_expiry_raw' => null,
            'training_due' => '—',
            'training_due_raw' => null,
            'performance_alerts' => '—',
            'performance_alerts_raw' => null,
            'links' => [],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<string, array<string, mixed>>  $kpiMap
     * @return array{raw: int|float|null, display: string}
     */
    protected function metric(\Illuminate\Support\Collection $kpiMap, string $key): array
    {
        $kpi = $kpiMap->get($key);

        if ($kpi === null) {
            return ['raw' => null, 'display' => '—'];
        }

        $value = $kpi['value'] ?? '—';

        if ($key === 'overtime_cost') {
            $raw = (float) str_replace([',', '%'], '', (string) $value);

            return [
                'raw' => $raw,
                'display' => is_numeric(str_replace(',', '', (string) $value))
                    ? $this->queries->money($raw)
                    : (string) $value,
            ];
        }

        if ($key === 'attendance_percent') {
            $raw = (float) str_replace('%', '', (string) $value);

            return ['raw' => $raw, 'display' => (string) $value];
        }

        $raw = is_numeric($value) ? (int) $value : (int) preg_replace('/\D/', '', (string) $value);

        return ['raw' => $raw, 'display' => (string) $value];
    }

    protected function performanceAlerts(int $companyId, ?int $branchId): int
    {
        if (! Schema::hasTable('performance_reviews')) {
            return 0;
        }

        return PerformanceReview::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereIn('rating', [
                PerformanceRating::Poor->value,
                PerformanceRating::Critical->value,
            ])
            ->whereDate('period_end', '>=', now()->subMonths(12)->toDateString())
            ->count();
    }

    /**
     * @return list<array{label: string, route: string, url: string}>
     */
    protected function hrLinks(): array
    {
        $definitions = [
            [
                'label' => __('HR Intelligence'),
                'route' => 'admin.hr.kpi',
                'permission' => ['hr.kpi.view', 'kpi.view'],
            ],
            [
                'label' => __('Employee 360'),
                'route' => 'admin.employees.index',
                'permission' => ['hr.employee360.view', 'employees.manage'],
            ],
            [
                'label' => __('HR Dashboard'),
                'route' => 'admin.hr.dashboard',
                'permission' => ['hr.dashboard.view'],
            ],
            [
                'label' => __('HR Reports'),
                'route' => 'admin.reports.hr',
                'permission' => ['hr.reports.view', 'reports.view'],
            ],
        ];

        $user = auth()->user();
        $links = [];

        foreach ($definitions as $def) {
            if (! $user || ! $this->userCanAny($user, $def['permission'])) {
                continue;
            }

            if (! Route::has($def['route'])) {
                continue;
            }

            $links[] = [
                'label' => $def['label'],
                'route' => $def['route'],
                'url' => route($def['route']),
            ];
        }

        return $links;
    }

    protected function canView(): bool
    {
        $user = auth()->user();

        return $user && (
            $user->can('hr.dashboard.view')
            || $user->can('hr.kpi.view')
            || $user->can('hr.reports.view')
            || $user->can('kpi.view')
            || $user->can('reports.view')
            || $user->can('hr.attendance.view')
            || $user->can('hr.leave.view')
            || $user->can('hr.payroll.view')
        );
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userCanAny(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
