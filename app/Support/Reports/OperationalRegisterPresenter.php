<?php

namespace App\Support\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class OperationalRegisterPresenter
{
    public function __construct(
        protected OperationalRegisterScopeResolver $scopeResolver,
        protected OperationalRegisterQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];
        $register = $resolved['register'];
        $user = $request->user();
        $registers = $this->availableRegisters($user);

        if (! array_key_exists($register, $registers)) {
            $register = array_key_first($registers) ?? 'daily_sales';
            $scope = new OperationalRegisterScope(
                companyId: $scope->companyId,
                branchId: $scope->branchId,
                fromDate: $scope->fromDate,
                toDate: $scope->toDate,
                register: $register,
            );
        }

        $tabData = $this->presentRegister($scope, $register, $user);
        $kpis = $this->kpiStrip($scope, $user);

        return [
            'title' => __(config('production_operational_registers.title', 'Operational Registers')),
            'description' => __(config('production_operational_registers.description', '')),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'registers' => $registers,
            'active_register' => $register,
            'active_register_label' => $registers[$register]['label'] ?? $register,
            'period_label' => $scope->fromDate.' — '.$scope->toDate,
            'branch_label' => $this->branchLabel($resolved['branches'], $scope->branchId),
            'presets' => config('production_operational_registers.presets', []),
            'executive_kpis' => $kpis,
            'tab_data' => $tabData,
            'export_url' => Route::has('admin.reports.operational-registers.export')
                ? route('admin.reports.operational-registers.export', $request->query())
                : null,
            'print_url' => Route::has('admin.reports.operational-registers.print')
                ? route('admin.reports.operational-registers.print', $request->query())
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentRegister(OperationalRegisterScope $scope, string $register, ?\App\Models\User $user = null): array
    {
        return match ($register) {
            'daily_sales' => $this->queries->dailySalesRegister($scope, $user),
            'digital', 'offset', 'outsource', 'large_format', 'finishing' => $this->queries->departmentRegister($scope, $register, $user),
            'production_summary' => $this->queries->productionSummaryRegister($scope),
            'machine_utilisation' => $this->queries->machineUtilisationRegister($scope),
            'operator_productivity' => $this->queries->operatorProductivityRegister($scope),
            'department_performance' => $this->queries->departmentPerformanceRegister($scope),
            default => ['summary' => [], 'table' => ['title' => '', 'columns' => [], 'rows' => []]],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function kpiStrip(OperationalRegisterScope $scope, ?\App\Models\User $user): array
    {
        $metrics = $this->queries->executiveKpis($scope, $user);

        return [
            $this->kpi(__('Sales today'), $metrics['sales_today'] ?? 0, 'currency-dollar', route('admin.reports.operational-registers', ['register' => 'daily_sales', 'preset' => 'today']), $user?->can('reports.view')),
            $this->kpi(__('Production value today'), $metrics['production_value_today'] ?? 0, 'cog', route('admin.reports.operational-registers', ['register' => 'production_summary', 'preset' => 'today']), $user?->can('reports.view')),
            $this->kpi(__('Completed today'), $metrics['jobs_completed_today'] ?? 0, 'check-circle', route('admin.production.queue.index'), $user?->can('production.queue.view')),
            $this->kpi(__('Running'), $metrics['jobs_running'] ?? 0, 'play', route('admin.production.queue.index', ['status' => 'in_progress']), $user?->can('production.queue.view')),
            $this->kpi(__('Waiting'), $metrics['jobs_waiting'] ?? 0, 'clock', route('admin.production.queue.index'), $user?->can('production.queue.view')),
            $this->kpi(__('Overdue'), $metrics['jobs_overdue'] ?? 0, 'exclamation', route('admin.production.queue.index', ['due' => 'overdue']), $user?->can('production.queue.view')),
            $this->kpi(__('Revenue today'), $metrics['revenue_today'] ?? 0, 'currency-dollar', route('admin.reports.operational-registers', ['register' => 'daily_sales', 'preset' => 'today']), $user?->can('reports.view')),
            $this->kpi(__('Outstanding'), $metrics['outstanding_payments'] ?? 0, 'credit-card', route('admin.reports.commercial'), $user?->can('reports.view')),
            $this->kpi(__('Outsourced'), $metrics['outsourced_jobs'] ?? 0, 'truck', route('admin.production.queue.department', 'outsource'), $user?->can('production.queue.view')),
            $this->kpi(__('Machine utilisation'), ($metrics['machine_utilisation'] ?? '—').(isset($metrics['machine_utilisation']) ? '%' : ''), 'server', route('admin.reports.operational-registers', ['register' => 'machine_utilisation']), $user?->can('reports.view')),
            $this->kpi(__('Dept utilisation'), ($metrics['department_utilisation'] ?? '—').(isset($metrics['department_utilisation']) ? '%' : ''), 'office-building', route('admin.reports.operational-registers', ['register' => 'department_performance']), $user?->can('reports.view')),
            $this->kpi(__('Operator productivity'), $metrics['operator_productivity'] ?? '—', 'users', route('admin.reports.operational-registers', ['register' => 'operator_productivity']), $user?->can('reports.view')),
        ];
    }

    /**
     * @return array{label: string, value: string, icon: string, url: ?string, clickable: bool}
     */
    protected function kpi(string $label, mixed $value, string $icon, ?string $url, bool $clickable): array
    {
        $formatted = is_numeric($value) && $value > 999
            ? number_format((float) $value, 0)
            : (string) $value;

        return [
            'label' => $label,
            'value' => $formatted,
            'icon' => $icon,
            'url' => $clickable ? $url : null,
            'clickable' => $clickable && $url !== null,
        ];
    }

    /**
     * @return array<string, array{label: string, permission: string}>
     */
    protected function availableRegisters(?\App\Models\User $user): array
    {
        $available = [];

        foreach (config('production_operational_registers.registers', []) as $key => $definition) {
            $permission = $definition['permission'] ?? 'reports.view';
            if ($user) {
                $allowed = collect(explode('|', $permission))
                    ->contains(fn (string $perm) => $user->can(trim($perm)));
                if (! $allowed) {
                    continue;
                }
            }

            $available[$key] = [
                'label' => __($definition['label']),
                'permission' => $permission,
            ];
        }

        return $available;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Branch>  $branches
     */
    protected function branchLabel($branches, ?int $branchId): string
    {
        if (! $branchId) {
            return __('All branches');
        }

        return $branches->firstWhere('id', $branchId)?->name ?? __('Branch');
    }
}
