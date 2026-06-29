<?php

namespace App\Support\Crm;

use App\Enums\CustomerPrintSpecificationStatus;
use App\Models\ActivityLog;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerPrintSpecification;
use App\Models\Production\ProductBom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerPrintSpecificationWorkspaceService
{
    public const TAB_OVERVIEW = 'overview';

    public const TAB_ARTWORK = 'artwork-versions';

    public const TAB_USAGE = 'usage-history';

    public const TAB_PRODUCTION = 'production-defaults';

    public const TAB_COMMERCIAL = 'commercial-defaults';

    public const TAB_TIMELINE = 'timeline';

    /** @var list<string> */
    public const TABS = [
        self::TAB_OVERVIEW,
        self::TAB_ARTWORK,
        self::TAB_USAGE,
        self::TAB_PRODUCTION,
        self::TAB_COMMERCIAL,
        self::TAB_TIMELINE,
    ];

    public function __construct(
        protected CustomerPrintSpecificationService $specifications,
        protected CustomerPrintSpecificationUsageService $usage,
        protected CustomerPrintSpecificationLifecycleService $lifecycle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(CustomerPrintSpecification $spec, ?string $tab = null): array
    {
        $activeTab = $this->resolveTab($tab);

        $spec->loadMissing([
            'customer:id,company_name,customer_code',
            'inventoryItem:id,item_name,sku,standard_cost,uses_serial_numbers,serial_prefix,serial_padding_length,stock_role',
            'activeArtworkVersion:id,customer_print_specification_id,version_number,original_file_name,file_name,status',
            'creator:id,name',
            'updater:id,name',
        ]);

        $usageMetrics = $this->usage->usageMetrics($spec);
        $serialSummary = $this->specifications->serialSummary($spec);
        $liveWarnings = $this->lifecycle->liveReferenceWarnings($spec);

        return [
            'specification' => $spec,
            'customer' => $spec->customer,
            'header' => $this->header($spec, $usageMetrics, $serialSummary),
            'summary_strip' => $this->summaryStrip($spec, $usageMetrics, $serialSummary),
            'usage_metrics' => $usageMetrics,
            'serial_summary' => $serialSummary,
            'production_intelligence' => $this->productionIntelligence($spec),
            'live_reference_warnings' => $liveWarnings,
            'allowed_transitions' => $spec->status->allowedTransitions(),
            'active_tab' => $activeTab,
            'tabs' => $this->tabNavigation($spec, $activeTab),
            'tab_data' => $this->tabData($spec, $activeTab),
        ];
    }

    public function resolveTab(?string $tab): string
    {
        $tab = $tab ?? self::TAB_OVERVIEW;

        return in_array($tab, self::TABS, true) ? $tab : self::TAB_OVERVIEW;
    }

    /**
     * @param  array<string, mixed>  $usageMetrics
     * @param  array<string, mixed>  $serialSummary
     * @return array<string, mixed>
     */
    protected function header(
        CustomerPrintSpecification $spec,
        array $usageMetrics,
        array $serialSummary,
    ): array {
        $activeArt = $spec->activeArtworkVersion;

        return [
            'code' => $spec->specification_code,
            'name' => $spec->name,
            'status' => $spec->status->label(),
            'status_value' => $spec->status->value,
            'product_name' => $spec->inventoryItem?->item_name,
            'artwork_version' => $activeArt?->versionLabel(),
            'orders_count' => $usageMetrics['orders_count'],
            'total_revenue' => $usageMetrics['total_revenue'],
            'is_read_only' => $spec->isReadOnly(),
            'has_operational_usage' => $spec->hasOperationalUsage(),
        ];
    }

    /**
     * @param  array<string, mixed>  $usageMetrics
     * @param  array<string, mixed>  $serialSummary
     * @return list<array<string, mixed>>
     */
    protected function summaryStrip(
        CustomerPrintSpecification $spec,
        array $usageMetrics,
        array $serialSummary,
    ): array {
        $activeArt = $spec->activeArtworkVersion;

        return [
            ['label' => __('Status'), 'value' => $spec->status->label()],
            ['label' => __('Orders'), 'value' => (string) $usageMetrics['orders_count']],
            ['label' => __('Revenue'), 'value' => number_format((float) $usageMetrics['total_revenue'], 2)],
            ['label' => __('Last ordered'), 'value' => $usageMetrics['last_ordered_at'] ? \Illuminate\Support\Carbon::parse($usageMetrics['last_ordered_at'])->format('Y-m-d') : '—'],
            ['label' => __('Artwork'), 'value' => $activeArt?->versionLabel() ?? '—'],
            ['label' => __('Serial'), 'value' => ($serialSummary['uses_serial_numbers'] ?? false)
                ? (($serialSummary['resolved_prefix'] ?? '').($serialSummary['next_number'] ?? '—'))
                : '—'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function productionIntelligence(CustomerPrintSpecification $spec): array
    {
        $item = $spec->inventoryItem;

        if (! $item) {
            return [];
        }

        $item->loadMissing(['activeProductionRouteSteps.workCenter:id,name']);

        $routeSteps = $item->activeProductionRouteSteps
            ->map(fn ($step) => [
                'sequence' => $step->sequence,
                'name' => $step->step_name,
                'work_center' => $step->workCenter?->name,
            ])
            ->values()
            ->all();

        $bom = ProductBom::query()
            ->forTenant()
            ->where('finished_item_id', $item->id)
            ->where('is_active', true)
            ->with(['lines.inventoryItem:id,item_name,standard_cost'])
            ->first();

        $materialCost = 0.0;

        if ($bom) {
            foreach ($bom->lines->where('is_active', true) as $line) {
                $qty = $line->effectiveQuantityPerUnit();
                $cost = (float) ($line->inventoryItem?->standard_cost ?? 0);
                $materialCost += $qty * $cost;
            }
        }

        $qc = app(\App\Support\Production\ProductQcChecklistService::class)
            ->findActiveForFinishedItem($spec->company_id, $spec->branch_id, $item->id);

        $serial = $this->specifications->serialSummary($spec);

        return [
            'route_steps' => $routeSteps,
            'route_label' => $routeSteps !== []
                ? collect($routeSteps)->pluck('name')->implode(' → ')
                : null,
            'bom_version' => $bom?->version,
            'bom_name' => $bom?->name,
            'qc_checklist' => $qc?->name,
            'qc_line_count' => $qc?->lines?->count() ?? 0,
            'estimated_duration_minutes' => count($routeSteps) > 0 ? count($routeSteps) * 30 : null,
            'estimated_material_cost' => round($materialCost, 2),
            'estimated_selling_price' => $spec->default_unit_price !== null
                ? (float) $spec->default_unit_price
                : (float) ($item->standard_cost ?? 0),
            'active_artwork' => $spec->activeArtworkVersion?->versionLabel(),
            'serial_rule' => ($serial['uses_serial_numbers'] ?? false)
                ? __('Prefix :prefix, next :number', [
                    'prefix' => $serial['resolved_prefix'] ?? '—',
                    'number' => $serial['next_number'] ?? '—',
                ])
                : __('No serial numbering'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function tabNavigation(CustomerPrintSpecification $spec, string $activeTab): array
    {
        $labels = [
            self::TAB_OVERVIEW => __('Overview'),
            self::TAB_ARTWORK => __('Artwork Versions'),
            self::TAB_USAGE => __('Usage History'),
            self::TAB_PRODUCTION => __('Production Defaults'),
            self::TAB_COMMERCIAL => __('Commercial Defaults'),
            self::TAB_TIMELINE => __('Timeline'),
        ];

        return collect(self::TABS)
            ->map(fn (string $id) => [
                'id' => $id,
                'label' => $labels[$id],
                'url' => route('admin.crm.customers.print-specifications.show', [
                    'customer' => $spec->customer_id,
                    'printSpecification' => $spec->id,
                    'tab' => $id,
                ]),
                'active' => $id === $activeTab,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function tabData(CustomerPrintSpecification $spec, string $tab): array
    {
        return match ($tab) {
            self::TAB_OVERVIEW => [
                'production' => $this->productionIntelligence($spec),
            ],
            self::TAB_ARTWORK => [
                'versions' => $this->artworkVersionHistory($spec),
            ],
            self::TAB_USAGE => $this->usage->usageHistory($spec),
            self::TAB_PRODUCTION => [
                'production_notes' => $spec->production_notes,
                'customer_instructions' => $spec->customer_instructions,
                'production' => $this->productionIntelligence($spec),
                'serial_summary' => $this->specifications->serialSummary($spec),
            ],
            self::TAB_COMMERCIAL => [
                'commercial_notes' => $spec->commercial_notes,
                'default_quantity' => $spec->default_quantity,
                'default_unit_price' => $spec->default_unit_price,
                'default_billing_type' => $spec->default_billing_type?->label(),
                'default_fulfilment_method' => $spec->default_fulfilment_method?->label(),
            ],
            self::TAB_TIMELINE => [
                'events' => $this->timelineEvents($spec),
            ],
            default => [],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function artworkVersionHistory(CustomerPrintSpecification $spec): array
    {
        return $spec->artworkVersions()
            ->with(['uploader:id,name', 'approver:id,name'])
            ->orderBy('version_number')
            ->get()
            ->map(fn ($version) => [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'label' => $version->versionLabel(),
                'is_current' => (bool) $version->is_active_version,
                'status' => $version->status->label(),
                'change_notes' => $version->change_notes ?: __('Initial version'),
                'uploaded_at' => $version->uploaded_at ?? $version->created_at,
                'uploaded_by' => $version->uploader?->name,
                'preview_url' => $version->previewUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function timelineEvents(CustomerPrintSpecification $spec): array
    {
        $events = [];

        $events[] = [
            'at' => $spec->created_at,
            'label' => __('Specification created'),
            'detail' => $spec->name,
            'user' => $spec->creator?->name,
            'kind' => 'created',
        ];

        foreach ($this->artworkVersionHistory($spec) as $version) {
            $events[] = [
                'at' => $version['uploaded_at'],
                'label' => __('Artwork :version', ['version' => $version['label']]),
                'detail' => $version['change_notes'],
                'user' => $version['uploaded_by'],
                'kind' => 'artwork',
                'is_current' => $version['is_current'],
            ];
        }

        ActivityLog::query()
            ->forTenant()
            ->where('model_type', CustomerPrintSpecification::class)
            ->where('model_id', $spec->id)
            ->where('action', 'updated')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->each(function (ActivityLog $log) use (&$events) {
                $changes = collect($log->properties ?? [])
                    ->except(['updated_at', 'updated_by'])
                    ->keys()
                    ->implode(', ');

                if ($changes === '') {
                    return;
                }

                $events[] = [
                    'at' => $log->created_at,
                    'label' => __('Specification updated'),
                    'detail' => $changes,
                    'user' => $log->user?->name,
                    'kind' => 'update',
                ];
            });

        usort($events, fn (array $a, array $b) => ($b['at'] ?? now()) <=> ($a['at'] ?? now()));

        return $events;
    }
}
