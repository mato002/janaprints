<?php

namespace App\Support\Reports;

use App\Models\Branch;
use Illuminate\Http\Request;

class IntelligenceReportPresenter
{
    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys(config('intelligence_reports', []));
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, config('intelligence_reports', []));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function definition(string $key): ?array
    {
        return config("intelligence_reports.{$key}");
    }

    public function permissionFor(string $key): string
    {
        return $this->definition($key)['permission'] ?? 'reports.view';
    }

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request, string $key): array
    {
        $definition = $this->definition($key);

        abort_if($definition === null, 404);

        ['companyId' => $companyId, 'branchId' => $defaultBranchId] = $this->tenantIds();

        $filters = [
            'from_date' => $request->input('from_date', now()->startOfMonth()->toDateString()),
            'to_date' => $request->input('to_date', now()->toDateString()),
            'branch_id' => $request->has('branch_id')
                ? ($request->input('branch_id') !== '' ? (int) $request->input('branch_id') : null)
                : $defaultBranchId,
        ];

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $widgets = collect($definition['widgets'] ?? [])
            ->map(fn (array $widget) => [
                'label' => $widget['label'],
                'icon' => $widget['icon'] ?? 'chart-pie',
                'value' => '—',
                'hint' => ! empty($widget['placeholder'])
                    ? __('Placeholder — module not connected yet')
                    : __('No data for selected filters'),
                'placeholder' => (bool) ($widget['placeholder'] ?? false),
            ])
            ->values()
            ->all();

        return [
            'key' => $key,
            'title' => $definition['title'],
            'description' => $definition['description'],
            'filters' => $filters,
            'branches' => $branches,
            'widgets' => $widgets,
            'has_data' => false,
            'can_export' => $request->user()?->can('reports.export') ?? false,
        ];
    }

    /**
     * @return array{companyId: int, branchId: int|null}
     */
    protected function tenantIds(): array
    {
        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;

        if (! $companyId) {
            abort(403, __('Company context is required.'));
        }

        return [
            'companyId' => (int) $companyId,
            'branchId' => tenant()->branchId(),
        ];
    }
}
