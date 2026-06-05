<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Enums\MaintenanceType;
use App\Enums\MaintenanceWorkOrderStatus;
use App\Models\ActivityLog;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\PlatformCacheService;

class Asset360Service
{
    public const TAB_OVERVIEW = 'overview';
    public const TAB_FINANCIAL = 'financial';
    public const TAB_MAINTENANCE = 'maintenance';
    public const TAB_UTILIZATION = 'utilization';
    public const TAB_CUSTODY = 'custody';
    public const TAB_PROCUREMENT = 'procurement';
    public const TAB_DOCUMENTS = 'documents';
    public const TAB_LIFECYCLE = 'lifecycle';

    /** @var list<string> */
    public const TABS = [
        self::TAB_OVERVIEW,
        self::TAB_FINANCIAL,
        self::TAB_MAINTENANCE,
        self::TAB_UTILIZATION,
        self::TAB_CUSTODY,
        self::TAB_PROCUREMENT,
        self::TAB_DOCUMENTS,
        self::TAB_LIFECYCLE,
    ];

    public function __construct(
        protected DepreciationCalculationService $depreciation,
        protected AssetHealthScoreService $health,
        protected AssetReplacementService $replacement,
        protected MachineShowService $machineShow,
        protected PlatformCacheService $cache,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(FixedAsset $asset, ?string $tab = null): array
    {
        $activeTab = $this->resolveTab($tab);
        $cacheKey = "{$asset->id}:{$activeTab}";

        return $this->cache->remember('asset_360', $cacheKey, function () use ($asset, $activeTab) {
            $this->loadBaseRelations($asset);
            $health = $this->health->score($asset);

            return [
                'asset' => $asset,
                'active_tab' => $activeTab,
                'tabs' => $this->tabNavigation($activeTab),
                'header' => $this->header($asset, $health),
                'health' => $health,
                'tab_data' => $this->tabData($asset, $activeTab, $health),
            ];
        }, config('platform.cache.asset_360', 60));
    }

    public function resolveTab(?string $tab): string
    {
        $tab = $tab ?? self::TAB_OVERVIEW;

        return in_array($tab, self::TABS, true) ? $tab : self::TAB_OVERVIEW;
    }

    protected function loadBaseRelations(FixedAsset $asset): void
    {
        $asset->loadMissing([
            'category', 'branch', 'vendor',
            'assignedUser', 'assignedBranch', 'assignedEmployee', 'assignedDepartment', 'custodian',
            'machineProfile.workCenter',
            'warranties' => fn ($q) => $q->latest('warranty_end')->limit(3),
        ]);
    }

    /**
     * @param  array{score: int, band: \App\Enums\AssetHealthBand}  $health
     * @return array<string, mixed>
     */
    protected function header(FixedAsset $asset, array $health): array
    {
        $financial = $this->depreciation->financialProfile($asset);
        $ageYears = ($asset->capitalization_date ?? $asset->acquisition_date)?->diffInYears(now()) ?? 0;
        $warranty = $asset->warranties->first();

        return [
            'asset_number' => $asset->asset_number,
            'asset_name' => $asset->asset_name,
            'category' => $asset->category?->name,
            'status' => $asset->status,
            'branch' => $asset->branch?->name,
            'department' => $asset->assignedDepartment?->name,
            'custodian' => $asset->custodian?->name ?? $asset->assignedUser?->name ?? $asset->assignedEmployee?->full_name,
            'acquisition_cost' => (float) $asset->acquisition_cost,
            'net_book_value' => $financial['net_book_value'],
            'age_years' => $ageYears,
            'warranty_status' => $warranty?->status?->label() ?? __('None'),
            'utilization_status' => $asset->machineProfile
                ? round((float) $asset->machineProfile->current_utilization, 1).'%'
                : ($asset->assigned_to_user_id || $asset->assigned_to_employee_id ? __('Assigned') : __('Unassigned')),
            'health_score' => $health['score'],
            'health_band' => $health['band'],
        ];
    }

    /**
     * @return list<array{key: string, label: string, active: bool}>
     */
    protected function tabNavigation(string $activeTab): array
    {
        $labels = [
            self::TAB_OVERVIEW => __('Overview'),
            self::TAB_FINANCIAL => __('Financial'),
            self::TAB_MAINTENANCE => __('Maintenance'),
            self::TAB_UTILIZATION => __('Utilization'),
            self::TAB_CUSTODY => __('Custody'),
            self::TAB_PROCUREMENT => __('Procurement'),
            self::TAB_DOCUMENTS => __('Documents'),
            self::TAB_LIFECYCLE => __('Lifecycle'),
        ];

        return collect(self::TABS)->map(fn (string $key) => [
            'key' => $key,
            'label' => $labels[$key],
            'active' => $key === $activeTab,
        ])->all();
    }

    /**
     * @param  array{score: int, band: \App\Enums\AssetHealthBand, factors: list<mixed>}  $health
     * @return array<string, mixed>
     */
    protected function tabData(FixedAsset $asset, string $tab, array $health): array
    {
        return match ($tab) {
            self::TAB_FINANCIAL => $this->financialTab($asset),
            self::TAB_MAINTENANCE => $this->maintenanceTab($asset),
            self::TAB_UTILIZATION => $this->utilizationTab($asset),
            self::TAB_CUSTODY => $this->custodyTab($asset),
            self::TAB_PROCUREMENT => $this->procurementTab($asset),
            self::TAB_DOCUMENTS => $this->documentsTab($asset),
            self::TAB_LIFECYCLE => $this->lifecycleTab($asset),
            default => $this->overviewTab($asset, $health),
        };
    }

    /**
     * @param  array{score: int, band: \App\Enums\AssetHealthBand, factors: list<mixed>}  $health
     * @return array<string, mixed>
     */
    protected function overviewTab(FixedAsset $asset, array $health): array
    {
        $financial = $this->depreciation->financialProfile($asset);
        $replacement = $this->replacement->candidates($asset->company_id, $asset->branch_id, 1)
            ->first(fn ($c) => $c['asset']->id === $asset->id);

        return [
            'kpis' => [
                ['label' => __('Book Value'), 'value' => number_format($financial['net_book_value'], 2)],
                ['label' => __('Health Score'), 'value' => $health['score']],
                ['label' => __('Open Work Orders'), 'value' => $asset->maintenanceWorkOrders()
                    ->whereIn('status', [MaintenanceWorkOrderStatus::Open, MaintenanceWorkOrderStatus::InProgress, MaintenanceWorkOrderStatus::Assigned])
                    ->count()],
                ['label' => __('Age (Years)'), 'value' => ($asset->capitalization_date ?? $asset->acquisition_date)?->diffInYears(now()) ?? 0],
            ],
            'replacement_candidate' => $replacement,
            'health_factors' => $health['factors'],
        ];
    }

    /** @return array<string, mixed> */
    protected function financialTab(FixedAsset $asset): array
    {
        $profile = $this->depreciation->financialProfile($asset);
        $timeline = $asset->financeTimelineEntries()->with('user:id,name')->limit(20)->get();
        $disposal = $asset->disposal;

        return [
            'profile' => $profile,
            'roi_placeholder' => null,
            'disposal_value' => $disposal?->disposal_proceeds,
            'replacement_estimate' => $profile['net_book_value'],
            'timeline' => $timeline,
        ];
    }

    /** @return array<string, mixed> */
    protected function maintenanceTab(FixedAsset $asset): array
    {
        $openStatuses = [
            MaintenanceWorkOrderStatus::Open,
            MaintenanceWorkOrderStatus::Assigned,
            MaintenanceWorkOrderStatus::InProgress,
            MaintenanceWorkOrderStatus::WaitingParts,
            MaintenanceWorkOrderStatus::WaitingVendor,
        ];

        $workOrders = $asset->maintenanceWorkOrders()->with('vendor:id,vendor_name')->latest('opened_at')->limit(15)->get();
        $downtimeMinutes = (int) $asset->downtimeRecords()->sum('duration_minutes');
        $timeline = $asset->maintenanceTimelineEntries()->with('user:id,name')->limit(20)->get();

        return [
            'work_orders' => $workOrders,
            'downtime_hours' => round($downtimeMinutes / 60, 1),
            'downtime_cost_placeholder' => null,
            'preventive_count' => $workOrders->filter(fn ($wo) => $wo->maintenance_type === MaintenanceType::Preventive)->count(),
            'corrective_count' => $workOrders->filter(fn ($wo) => in_array($wo->maintenance_type, [MaintenanceType::Corrective, MaintenanceType::Emergency], true))->count(),
            'upcoming' => $asset->maintenancePlans()->where('next_due_date', '>=', now())->orderBy('next_due_date')->limit(5)->get(),
            'overdue' => $asset->maintenancePlans()->where('next_due_date', '<', now())->orderBy('next_due_date')->limit(5)->get(),
            'open_count' => $asset->maintenanceWorkOrders()->whereIn('status', $openStatuses)->count(),
            'timeline' => $timeline,
        ];
    }

    /** @return array<string, mixed> */
    protected function utilizationTab(FixedAsset $asset): array
    {
        if ($asset->machineProfile) {
            $machine = $this->machineShow->build($asset);

            return [
                'type' => 'machine',
                'capacity' => $machine['capacity'],
                'availability' => $machine['availability'],
                'assigned_jobs' => $machine['assigned_jobs'],
                'utilization' => $machine['capacity']['current_utilization'] ?? 0,
            ];
        }

        $daysAssigned = $asset->assignmentHistories()->count();
        $utilizationPct = $asset->assigned_to_user_id || $asset->assigned_to_employee_id ? 98 : ($daysAssigned > 0 ? 75 : 0);

        return [
            'type' => 'general',
            'assignment_utilization' => $utilizationPct,
            'assignment_histories' => $asset->assignmentHistories()->with(['assignedUser', 'assignedEmployee'])->limit(10)->get(),
            'custody_status' => $asset->custody_status?->label(),
        ];
    }

    /** @return array<string, mixed> */
    protected function custodyTab(FixedAsset $asset): array
    {
        return [
            'current_custodian' => $asset->custodian?->name ?? $asset->assignedUser?->name ?? $asset->assignedEmployee?->full_name,
            'assignment_histories' => $asset->assignmentHistories()->with(['assigner', 'assignedUser', 'assignedEmployee', 'assignedBranch'])->limit(20)->get(),
            'handovers' => $asset->handovers()->limit(10)->get(),
            'returns' => $asset->assetReturns()->limit(10)->get(),
            'transfers' => $asset->branchTransfers()->limit(10)->get(),
            'condition_histories' => $asset->conditionHistories()->limit(10)->get(),
            'timeline' => $asset->custodyTimelineEntries()->with('user:id,name')->limit(25)->get(),
        ];
    }

    /** @return array<string, mixed> */
    protected function procurementTab(FixedAsset $asset): array
    {
        $asset->loadMissing([
            'purchaseRequest', 'purchaseOrder', 'goodsReceipt', 'supplierBill', 'vendor',
            'capitalizationCandidate', 'acquisitionJournal', 'procurementDocuments',
        ]);

        $timeline = collect([
            $asset->purchaseRequest ? ['event' => __('Purchase Request'), 'ref' => $asset->purchaseRequest->request_number, 'date' => $asset->purchaseRequest->created_at] : null,
            $asset->purchaseOrder ? ['event' => __('Purchase Order'), 'ref' => $asset->purchaseOrder->po_number, 'date' => $asset->purchaseOrder->order_date] : null,
            $asset->goodsReceipt ? ['event' => __('Goods Receipt'), 'ref' => $asset->goodsReceipt->receipt_number, 'date' => $asset->goodsReceipt->receipt_date] : null,
            $asset->capitalizationCandidate ? ['event' => __('Capitalization'), 'ref' => $asset->capitalizationCandidate->candidate_number, 'date' => $asset->capitalization_date] : null,
            $asset->acquisitionJournal ? ['event' => __('Accounting Posted'), 'ref' => $asset->acquisitionJournal->journal_number ?? $asset->acquisitionJournal->id, 'date' => $asset->acquisitionJournal->journal_date] : null,
        ])->filter()->values();

        return [
            'vendor' => $asset->vendor,
            'purchase_request' => $asset->purchaseRequest,
            'purchase_order' => $asset->purchaseOrder,
            'goods_receipt' => $asset->goodsReceipt,
            'supplier_bill' => $asset->supplierBill,
            'capitalization' => $asset->capitalizationCandidate,
            'warranties' => $asset->warranties,
            'timeline' => $timeline,
        ];
    }

    /** @return array<string, mixed> */
    protected function documentsTab(FixedAsset $asset): array
    {
        $docs = $asset->procurementDocuments()->get();
        $handovers = $asset->handovers()->limit(5)->get(['id', 'handover_no', 'handover_date']);
        $transfers = $asset->branchTransfers()->limit(5)->get(['id', 'transfer_no', 'requested_at']);

        return [
            'procurement_documents' => $docs,
            'handovers' => $handovers,
            'transfers' => $transfers,
            'warranties' => $asset->warranties,
        ];
    }

    /** @return array<string, mixed> */
    protected function lifecycleTab(FixedAsset $asset): array
    {
        $events = collect()
            ->merge($asset->financeTimelineEntries()->get()->map(fn ($e) => $this->mapTimeline($e, 'finance')))
            ->merge($asset->custodyTimelineEntries()->get()->map(fn ($e) => $this->mapTimeline($e, 'custody')))
            ->merge($asset->maintenanceTimelineEntries()->get()->map(fn ($e) => $this->mapTimeline($e, 'maintenance')))
            ->merge($asset->machineTimelineEntries()->get()->map(fn ($e) => $this->mapTimeline($e, 'machine')))
            ->sortByDesc('occurred_at')
            ->take(40)
            ->values();

        $lifePercent = $this->health->agePercentOfLife($asset);

        return [
            'timeline' => $events,
            'lifecycle_progress' => round($lifePercent, 1),
            'milestones' => [
                'acquired' => $asset->acquisition_date,
                'capitalized' => $asset->capitalization_date,
                'disposed' => $asset->status === FixedAssetStatus::Disposed ? $asset->disposal?->disposal_date : null,
            ],
        ];
    }

    /** @return array<string, mixed> */
    protected function mapTimeline(object $entry, string $domain): array
    {
        return [
            'domain' => $domain,
            'event_type' => $entry->event_type,
            'title' => $entry->title,
            'description' => $entry->description,
            'occurred_at' => $entry->occurred_at,
            'user' => $entry->user?->name ?? null,
        ];
    }
}
