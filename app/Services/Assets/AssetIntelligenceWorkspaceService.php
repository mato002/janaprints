<?php

namespace App\Services\Assets;

use App\Models\Branch;
use Illuminate\Http\Request;

class AssetIntelligenceWorkspaceService
{
    public function __construct(
        protected AssetExecutiveIntelligenceService $executive,
        protected AssetBranchIntelligenceService $branch,
        protected AssetAnalyticsService $analytics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $activeTab = $this->resolveTab($request);
        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $payload = [
            'activeTab' => $activeTab,
            'tabs' => $this->tabs(),
            'hubUrl' => route('admin.assets.intelligence.dashboard'),
        ];

        return match ($activeTab) {
            'branch' => array_merge($payload, $this->branchTab($request, $companyId)),
            'analytics' => array_merge($payload, [
                'stats' => $this->analytics->build($companyId, $branchId),
            ]),
            default => array_merge($payload, [
                'stats' => $this->executive->build($companyId, $branchId),
            ]),
        };
    }

    public function resolveTab(Request $request): string
    {
        $tab = (string) $request->query('tab', 'overview');

        $tab = match ($tab) {
            'executive-asset-dashboard', 'executive', 'intelligence' => 'overview',
            'branch-asset-intelligence' => 'branch',
            'asset-analytics-center' => 'analytics',
            default => $tab,
        };

        return in_array($tab, array_keys($this->tabs()), true) ? $tab : 'overview';
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        return [
            'overview' => __('Executive Overview'),
            'branch' => __('Branch Intelligence'),
            'analytics' => __('Analytics Center'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchTab(Request $request, int $companyId): array
    {
        $branchId = (int) ($request->integer('branch_id') ?: tenant()->branchId() ?: Branch::query()->where('company_id', $companyId)->value('id'));

        return [
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'selected_branch_id' => $branchId,
            'stats' => $this->branch->build($companyId, $branchId),
        ];
    }
}
