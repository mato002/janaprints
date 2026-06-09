<?php

namespace App\Support\Dashboard;

use App\Models\User;
use App\Services\Assets\AssetExecutiveIntelligenceService;
use App\Services\Assets\MaintenanceDashboardService;
use App\Support\Reports\IntelligenceAggregateQueries;
use Illuminate\Support\Facades\Route;

class ExecutiveAssetIntelligenceService
{
    public function __construct(
        protected AssetExecutiveIntelligenceService $assetIntelligence,
        protected MaintenanceDashboardService $maintenance,
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
        $stats = $this->assetIntelligence->build($companyId, $branchId);
        $maintenance = $this->maintenance->build($companyId, $branchId);

        $assetCount = (int) ($stats['asset_count'] ?? 0);
        $netBookValue = (float) ($stats['net_book_value'] ?? 0);
        $depreciationMtd = (float) ($stats['depreciation_this_month'] ?? 0);
        $requiringService = (int) ($stats['assets_under_maintenance'] ?? 0)
            + (int) ($maintenance['overdue_maintenance'] ?? 0);

        $available = $assetCount > 0
            || $netBookValue > 0
            || $requiringService > 0;

        return [
            'visible' => true,
            'available' => $available,
            'source' => 'asset_intelligence',
            'asset_count' => (string) $assetCount,
            'asset_count_raw' => $assetCount,
            'net_book_value' => $netBookValue > 0 ? $this->queries->money($netBookValue) : '—',
            'net_book_value_raw' => $netBookValue > 0 ? $netBookValue : null,
            'depreciation_mtd' => $depreciationMtd > 0 ? $this->queries->money($depreciationMtd) : '—',
            'depreciation_mtd_raw' => $depreciationMtd > 0 ? $depreciationMtd : null,
            'warranty_expiry' => (string) ($stats['warranty_expiring'] ?? 0),
            'warranty_expiry_raw' => (int) ($stats['warranty_expiring'] ?? 0),
            'requiring_service' => (string) $requiringService,
            'requiring_service_raw' => $requiringService,
            'critical_assets' => (string) ($stats['critical_assets'] ?? 0),
            'critical_assets_raw' => (int) ($stats['critical_assets'] ?? 0),
            'end_of_life' => (string) ($stats['assets_near_end_of_life'] ?? 0),
            'end_of_life_raw' => (int) ($stats['assets_near_end_of_life'] ?? 0),
            'links' => $this->assetLinks(),
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
            'asset_count' => '—',
            'asset_count_raw' => null,
            'net_book_value' => '—',
            'net_book_value_raw' => null,
            'depreciation_mtd' => '—',
            'depreciation_mtd_raw' => null,
            'warranty_expiry' => '—',
            'warranty_expiry_raw' => null,
            'requiring_service' => '—',
            'requiring_service_raw' => null,
            'critical_assets' => '—',
            'critical_assets_raw' => null,
            'end_of_life' => '—',
            'end_of_life_raw' => null,
            'links' => [],
        ];
    }

    /**
     * @return list<array{label: string, route: string, url: string}>
     */
    protected function assetLinks(): array
    {
        $definitions = [
            [
                'label' => __('Asset Executive Dashboard'),
                'route' => 'admin.assets.intelligence.executive',
                'permission' => ['assets.analytics.view', 'intelligence.assets.view'],
            ],
            [
                'label' => __('Maintenance Center'),
                'route' => 'admin.assets.maintenance.dashboard',
                'permission' => ['maintenance.view'],
            ],
            [
                'label' => __('Asset Register'),
                'route' => 'admin.assets.index',
                'permission' => ['assets.view'],
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
            $user->can('assets.analytics.view')
            || $user->can('assets.view')
            || $user->can('maintenance.view')
            || $user->can('intelligence.assets.view')
            || $user->can('reports.view')
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
