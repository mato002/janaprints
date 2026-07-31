<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\InventoryRiskLevel;
use App\Enums\InventoryStockRole;
use App\Enums\InventoryVelocityClass;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\DeadStockDetectionService;
use App\Services\Inventory\InventoryVelocityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryIntelligenceController extends Controller
{
    public function __construct(
        protected InventoryVelocityService $velocityService,
        protected DeadStockDetectionService $deadStockService,
    ) {}

    public function overview(Request $request): View
    {
        $this->authorizeIntelligence();

        [$companyId, $branchId] = $this->tenantScope();
        $window = (int) $request->input('window', config('inventory_intelligence.default_snapshot_window', 30));

        $counts = $this->velocityService->overviewCounts($companyId, $branchId, $window);

        return view('admin.inventory.intelligence.overview', [
            'counts' => $counts,
            'window' => $window,
            'topStockoutRisks' => $this->velocityService->latestSnapshots(
                $companyId,
                $branchId,
                $window,
                limit: 10,
            )->filter(fn ($row) => in_array($row->risk_level, [
                InventoryRiskLevel::Critical,
                InventoryRiskLevel::High,
                InventoryRiskLevel::Medium,
            ], true))->take(10),
            'topDeadStock' => $this->deadStockService->detect($companyId, [
                'branch_id' => $branchId,
            ])->take(10),
            'fastRawMaterials' => $this->velocityService->latestSnapshots(
                $companyId,
                $branchId,
                $window,
                velocityClass: InventoryVelocityClass::FastMoving,
                limit: 10,
            )->filter(fn ($row) => ($row->stock_role ?? null) === InventoryStockRole::RawMaterial->value),
            'stagnantFinishedGoods' => $this->velocityService->latestSnapshots(
                $companyId,
                $branchId,
                $window,
                velocityClass: InventoryVelocityClass::DeadStock,
                limit: 10,
            )->filter(fn ($row) => ($row->stock_role ?? null) === InventoryStockRole::FinishedGood->value),
        ]);
    }

    public function stockoutRisk(Request $request): View
    {
        $this->authorizeIntelligence();

        [$companyId, $branchId] = $this->tenantScope();
        $window = (int) $request->input('window', config('inventory_intelligence.default_snapshot_window', 30));

        return view('admin.inventory.intelligence.stockout-risk', [
            'window' => $window,
            'snapshots' => $this->velocityService->latestSnapshots($companyId, $branchId, $window, limit: 100)
                ->filter(fn ($row) => in_array($row->risk_level, [
                    InventoryRiskLevel::Critical,
                    InventoryRiskLevel::High,
                    InventoryRiskLevel::Medium,
                ], true)),
        ]);
    }

    public function deadStock(Request $request): View
    {
        $this->authorizeIntelligence();

        [$companyId, $branchId] = $this->tenantScope();

        return view('admin.inventory.intelligence.dead-stock', [
            'rows' => $this->deadStockService->detect($companyId, [
                'branch_id' => $branchId,
                'warehouse_id' => $request->integer('warehouse_id') ?: null,
                'stock_role' => $request->input('stock_role'),
                'category_id' => $request->integer('category_id') ?: null,
                'subcategory_id' => $request->integer('subcategory_id') ?: null,
            ]),
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => InventoryCategory::query()->forTenant()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['warehouse_id', 'stock_role', 'category_id', 'subcategory_id']),
        ]);
    }

    public function fastMovers(Request $request): View
    {
        $this->authorizeIntelligence();

        [$companyId, $branchId] = $this->tenantScope();
        $window = (int) $request->input('window', config('inventory_intelligence.default_snapshot_window', 30));

        return view('admin.inventory.intelligence.fast-movers', [
            'window' => $window,
            'snapshots' => $this->velocityService->latestSnapshots(
                $companyId,
                $branchId,
                $window,
                velocityClass: InventoryVelocityClass::FastMoving,
                limit: 100,
            ),
        ]);
    }

    public function slowMovers(Request $request): View
    {
        $this->authorizeIntelligence();

        [$companyId, $branchId] = $this->tenantScope();
        $window = (int) $request->input('window', config('inventory_intelligence.default_snapshot_window', 30));

        return view('admin.inventory.intelligence.slow-movers', [
            'window' => $window,
            'snapshots' => $this->velocityService->latestSnapshots(
                $companyId,
                $branchId,
                $window,
                velocityClass: InventoryVelocityClass::SlowMoving,
                limit: 100,
            ),
        ]);
    }

    public function warehouseVelocity(Request $request): View
    {
        $this->authorizeIntelligence();

        [$companyId, $branchId] = $this->tenantScope();
        $window = (int) $request->input('window', config('inventory_intelligence.default_snapshot_window', 30));

        return view('admin.inventory.intelligence.warehouse-velocity', [
            'window' => $window,
            'rows' => $this->velocityService->warehouseVelocitySummary($companyId, $branchId, $window),
        ]);
    }

    public function settings(): View
    {
        abort_unless(auth()->user()?->can('inventory.intelligence.configure'), 403);

        return view('admin.inventory.intelligence.settings', [
            'config' => config('inventory_intelligence'),
        ]);
    }

    public function generate(): \Illuminate\Http\RedirectResponse
    {
        abort_unless(auth()->user()?->can('inventory.intelligence.generate'), 403);

        $companyId = (int) (tenant()->companyId() ?? auth()->user()?->company_id);
        $branchId = tenant()->branchId();

        $this->velocityService->generateSnapshots(
            companyId: $companyId,
            branchId: $branchId,
            windows: config('inventory_intelligence.windows', [30]),
            dryRun: false,
            syncAlerts: true,
        );

        return redirect()
            ->route('admin.inventory.intelligence.overview')
            ->with('status', __('Velocity snapshots refreshed.'));
    }

    protected function authorizeIntelligence(): void
    {
        abort_unless(auth()->user()?->can('inventory.intelligence.view'), 403);
    }

    /**
     * @return array{0: int, 1: int|null}
     */
    protected function tenantScope(): array
    {
        return [
            (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            tenant()->branchId(),
        ];
    }
}
