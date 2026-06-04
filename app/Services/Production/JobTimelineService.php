<?php

namespace App\Services\Production;

use App\DataTransferObjects\Production\JobTimelineEvent;
use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class JobTimelineService
{
    public const PER_PAGE = 30;

    public const FILTER_ALL = 'all';

    public const FILTER_OPERATIONS = 'operations';

    public const FILTER_MATERIALS = 'materials';

    public const FILTER_QUALITY = 'quality';

    public const FILTER_TRACEABILITY = 'traceability';

    public const FILTER_DISPATCH = 'dispatch';

    /** @var list<string> */
    public const FILTERS = [
        self::FILTER_ALL,
        self::FILTER_OPERATIONS,
        self::FILTER_MATERIALS,
        self::FILTER_QUALITY,
        self::FILTER_TRACEABILITY,
        self::FILTER_DISPATCH,
    ];

    /**
     * @return array<string, mixed>
     */
    public function paginate(ProductionJobCard $jobCard, ?string $filter = null, ?string $search = null, ?int $page = null): array
    {
        $filter = $this->resolveFilter($filter);
        $search = $this->normalizeSearch($search);
        $page = max(1, (int) ($page ?? 1));

        $collectors = $this->collectorsForFilter($filter, $jobCard);
        $union = $this->buildUnionQuery($collectors, $jobCard);

        if ($union === null) {
            return $this->emptyPayload($page, $jobCard, $filter, $search);
        }

        $wrapped = DB::query()->fromSub($union, 'job_timeline');

        if ($search !== null) {
            $wrapped->where(function (Builder $query) use ($search) {
                $like = '%'.$search.'%';
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('metadata', 'like', $like);
            });
        }

        $total = (clone $wrapped)->count();

        $rows = $wrapped
            ->orderByDesc('event_datetime')
            ->orderByDesc('source_id')
            ->forPage($page, self::PER_PAGE)
            ->get();

        return [
            'events' => $this->mapRowsToPaginator($rows, $total, $page, $jobCard, $filter, $search),
            'filter' => $filter,
            'search' => $search,
            'filters' => $this->filterOptions(),
            'uses_union' => true,
        ];
    }

    public function resolveFilter(?string $filter): string
    {
        $filter = $filter ?? self::FILTER_ALL;

        return in_array($filter, self::FILTERS, true) ? $filter : self::FILTER_ALL;
    }

    public function normalizeSearch(?string $search): ?string
    {
        if ($search === null) {
            return null;
        }

        $search = trim($search);

        return ($search === '' || strlen($search) < 2) ? null : Str::limit($search, 100, '');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function filterOptions(): array
    {
        return [
            ['value' => self::FILTER_ALL, 'label' => __('All')],
            ['value' => self::FILTER_OPERATIONS, 'label' => __('Operations')],
            ['value' => self::FILTER_MATERIALS, 'label' => __('Materials')],
            ['value' => self::FILTER_QUALITY, 'label' => __('Quality')],
            ['value' => self::FILTER_TRACEABILITY, 'label' => __('Traceability')],
            ['value' => self::FILTER_DISPATCH, 'label' => __('Dispatch')],
        ];
    }

    /**
     * @return list<\Closure(ProductionJobCard): Builder>
     */
    protected function collectorsForFilter(string $filter, ProductionJobCard $jobCard): array
    {
        return match ($filter) {
            self::FILTER_OPERATIONS => [
                fn (ProductionJobCard $j) => $this->operationsQuery($j),
            ],
            self::FILTER_MATERIALS => [
                fn (ProductionJobCard $j) => $this->materialsQuery($j),
            ],
            self::FILTER_QUALITY => [
                fn (ProductionJobCard $j) => $this->qualityQuery($j),
            ],
            self::FILTER_TRACEABILITY => [
                fn (ProductionJobCard $j) => $this->artworkApprovalsQuery($j),
                fn (ProductionJobCard $j) => $this->salesOrderQuery($j),
            ],
            self::FILTER_DISPATCH => $this->deliveryNoteCollectors(),
            default => array_merge([
                fn (ProductionJobCard $j) => $this->jobLifecycleQuery($j),
                fn (ProductionJobCard $j) => $this->operationsQuery($j),
                fn (ProductionJobCard $j) => $this->materialsQuery($j),
                fn (ProductionJobCard $j) => $this->qualityQuery($j),
                fn (ProductionJobCard $j) => $this->artworkApprovalsQuery($j),
                fn (ProductionJobCard $j) => $this->salesOrderQuery($j),
            ], $this->deliveryNoteCollectors()),
        };
    }

    /**
     * @param  list<\Closure(ProductionJobCard): Builder>  $collectors
     */
    protected function buildUnionQuery(array $collectors, ProductionJobCard $jobCard): ?Builder
    {
        $union = null;

        foreach ($collectors as $collector) {
            $query = $collector($jobCard);
            $union = $union === null ? $query : $union->unionAll($query);
        }

        return $union;
    }

    protected function eventSelect(
        string $eventType,
        string $title,
        string $description,
        string $eventDatetime,
        string $actorName,
        string $actorType,
        string $sourceType,
        string $sourceId,
        ?string $sourceRoute,
        string $sourceRouteParam,
        string $icon,
        string $color,
        string $category,
        string $metadata = "'{}'",
    ): array {
        return [
            DB::raw("'{$eventType}' as event_type"),
            DB::raw($title.' as title'),
            DB::raw($description.' as description'),
            DB::raw($eventDatetime.' as event_datetime'),
            DB::raw($actorName.' as actor_name'),
            DB::raw("'{$actorType}' as actor_type"),
            DB::raw("'{$sourceType}' as source_type"),
            DB::raw($sourceId.' as source_id'),
            DB::raw(($sourceRoute ? "'{$sourceRoute}'" : 'NULL').' as source_route'),
            DB::raw("'{$sourceRouteParam}' as source_route_param"),
            DB::raw("'{$icon}' as icon"),
            DB::raw("'{$color}' as color"),
            DB::raw("'{$category}' as category"),
            DB::raw($metadata.' as metadata'),
        ];
    }

    protected function jobLifecycleQuery(ProductionJobCard $jobCard): Builder
    {
        $created = DB::table('production_job_cards')
            ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
            ->where('production_job_cards.id', $jobCard->id)
            ->select($this->eventSelect(
                eventType: 'JOB_CREATED',
                title: $this->sqlQuote(__('Job card created')),
                description: $this->sqlConcat('production_job_cards.job_card_number'),
                eventDatetime: 'production_job_cards.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'document-add',
                color: 'slate',
                category: 'operations',
            ));

        $started = DB::table('production_job_cards')
            ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
            ->where('production_job_cards.id', $jobCard->id)
            ->whereNotNull('production_job_cards.actual_start_date')
            ->select($this->eventSelect(
                eventType: 'JOB_STARTED',
                title: $this->sqlQuote(__('Production started')),
                description: 'production_job_cards.job_card_number',
                eventDatetime: 'production_job_cards.actual_start_date',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'play',
                color: 'amber',
                category: 'operations',
            ));

        $dispatch = DB::table('production_job_cards')
            ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
            ->where('production_job_cards.id', $jobCard->id)
            ->where('production_job_cards.status', ProductionJobCardStatus::ReadyForDispatch->value)
            ->select($this->eventSelect(
                eventType: 'DISPATCH_READY',
                title: $this->sqlQuote(__('Ready for dispatch')),
                description: 'production_job_cards.job_card_number',
                eventDatetime: 'production_job_cards.updated_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'truck',
                color: 'emerald',
                category: 'traceability',
            ));

        return $created->unionAll($started)->unionAll($dispatch);
    }

    protected function operationsQuery(ProductionJobCard $jobCard): Builder
    {
        $started = DB::table('production_operations')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_operations.production_job_card_id')
            ->leftJoin('work_centers', 'work_centers.id', '=', 'production_operations.work_center_id')
            ->leftJoin('production_stages', 'production_stages.id', '=', 'production_operations.production_stage_id')
            ->leftJoin('employees', 'employees.id', '=', 'production_operations.assigned_employee_id')
            ->where('production_operations.production_job_card_id', $jobCard->id)
            ->select($this->eventSelect(
                eventType: 'OPERATION_STARTED',
                title: $this->sqlConcat($this->sqlQuote(__('Operation started').' — '), 'COALESCE(work_centers.name, production_stages.name)'),
                description: 'COALESCE(production_operations.remarks, '."''".')',
                eventDatetime: 'production_operations.started_at',
                actorName: $this->sqlConcat('COALESCE(employees.first_name, '."''".')', "' '", 'COALESCE(employees.last_name, '."''".')'),
                actorType: 'user',
                sourceType: 'operation',
                sourceId: 'production_operations.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'cog',
                color: 'sky',
                category: 'operations',
            ));

        $completed = DB::table('production_operations')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_operations.production_job_card_id')
            ->leftJoin('work_centers', 'work_centers.id', '=', 'production_operations.work_center_id')
            ->leftJoin('production_stages', 'production_stages.id', '=', 'production_operations.production_stage_id')
            ->where('production_operations.production_job_card_id', $jobCard->id)
            ->whereNotNull('production_operations.ended_at')
            ->select($this->eventSelect(
                eventType: 'OPERATION_COMPLETED',
                title: $this->sqlConcat($this->sqlQuote(__('Operation completed').' — '), 'COALESCE(work_centers.name, production_stages.name)'),
                description: 'COALESCE(production_operations.remarks, '."''".')',
                eventDatetime: 'production_operations.ended_at',
                actorName: "'".__('System')."'",
                actorType: 'system',
                sourceType: 'operation',
                sourceId: 'production_operations.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'check-circle',
                color: 'emerald',
                category: 'operations',
            ));

        return $started->unionAll($completed);
    }

    protected function materialsQuery(ProductionJobCard $jobCard): Builder
    {
        return DB::table('production_material_consumptions')
            ->join('inventory_items', 'inventory_items.id', '=', 'production_material_consumptions.inventory_item_id')
            ->leftJoin('users', 'users.id', '=', 'production_material_consumptions.consumed_by')
            ->where('production_material_consumptions.production_job_card_id', $jobCard->id)
            ->select($this->eventSelect(
                eventType: 'MATERIAL_CONSUMED',
                title: $this->sqlQuote(__('Material consumed')),
                description: $this->sqlConcat('inventory_items.item_name', "' — '", 'production_material_consumptions.quantity'),
                eventDatetime: 'production_material_consumptions.consumed_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'material',
                sourceId: 'production_material_consumptions.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'cube',
                color: 'amber',
                category: 'materials',
            ));
    }

    protected function qualityQuery(ProductionJobCard $jobCard): Builder
    {
        return DB::table('quality_checks')
            ->leftJoin('users', 'users.id', '=', 'quality_checks.checked_by')
            ->where('quality_checks.production_job_card_id', $jobCard->id)
            ->select($this->eventSelect(
                eventType: 'QC_RECORDED',
                title: $this->sqlConcat($this->sqlQuote(__('Quality check').': '), 'quality_checks.result'),
                description: 'COALESCE(quality_checks.comments, '."''".')',
                eventDatetime: 'quality_checks.checked_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'quality_check',
                sourceId: 'quality_checks.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'badge-check',
                color: 'indigo',
                category: 'quality',
            ));
    }

    protected function artworkApprovalsQuery(ProductionJobCard $jobCard): Builder
    {
        if (! $jobCard->artwork_request_id) {
            return DB::table('production_job_cards')->whereRaw('1 = 0')->select($this->eventSelect(
                eventType: 'SKIP', title: "''", description: "''", eventDatetime: 'production_job_cards.created_at',
                actorName: "''", actorType: 'system', sourceType: 'skip', sourceId: 'production_job_cards.id',
                sourceRoute: null, sourceRouteParam: 'jobCard', icon: 'x', color: 'slate', category: 'traceability',
            ));
        }

        return DB::table('artwork_approvals')
            ->join('artwork_requests', 'artwork_requests.id', '=', 'artwork_approvals.artwork_request_id')
            ->leftJoin('users', 'users.id', '=', 'artwork_approvals.approved_by')
            ->where('artwork_approvals.artwork_request_id', $jobCard->artwork_request_id)
            ->select($this->eventSelect(
                eventType: 'ARTWORK_APPROVAL',
                title: $this->sqlConcat($this->sqlQuote(__('Artwork').' '), 'artwork_approvals.decision'),
                description: 'COALESCE(artwork_approvals.comments, artwork_requests.request_number)',
                eventDatetime: 'artwork_approvals.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'artwork',
                sourceId: 'artwork_requests.id',
                sourceRoute: 'admin.artwork.show',
                sourceRouteParam: 'artworkRequest',
                icon: 'color-swatch',
                color: 'pink',
                category: 'traceability',
            ));
    }

    protected function salesOrderQuery(ProductionJobCard $jobCard): Builder
    {
        if (! $jobCard->sales_order_id) {
            return DB::table('production_job_cards')->whereRaw('1 = 0')->select($this->eventSelect(
                eventType: 'SKIP', title: "''", description: "''", eventDatetime: 'production_job_cards.created_at',
                actorName: "''", actorType: 'system', sourceType: 'skip', sourceId: 'production_job_cards.id',
                sourceRoute: null, sourceRouteParam: 'jobCard', icon: 'x', color: 'slate', category: 'traceability',
            ));
        }

        return DB::table('sales_orders')
            ->where('sales_orders.id', $jobCard->sales_order_id)
            ->select($this->eventSelect(
                eventType: 'SALES_ORDER_LINKED',
                title: $this->sqlConcat($this->sqlQuote(__('Sales order').' '), 'sales_orders.order_number'),
                description: $this->sqlConcat($this->sqlQuote(__('Status').': '), 'sales_orders.status'),
                eventDatetime: 'sales_orders.updated_at',
                actorName: "'".__('System')."'",
                actorType: 'system',
                sourceType: 'sales_order',
                sourceId: 'sales_orders.id',
                sourceRoute: 'admin.sales-orders.show',
                sourceRouteParam: 'salesOrder',
                icon: 'shopping-cart',
                color: 'violet',
                category: 'traceability',
            ));
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyPayload(int $page, ProductionJobCard $jobCard, string $filter, ?string $search): array
    {
        return [
            'events' => $this->emptyPaginator($page, $jobCard, $filter, $search),
            'filter' => $filter,
            'search' => $search,
            'filters' => $this->filterOptions(),
            'uses_union' => false,
        ];
    }

    protected function mapRowsToPaginator($rows, int $total, int $page, ProductionJobCard $jobCard, string $filter, ?string $search): LengthAwarePaginator
    {
        $items = $rows
            ->filter(fn (object $row) => $row->event_type !== 'SKIP')
            ->map(fn (object $row) => JobTimelineEvent::fromRow($row));

        return new PaginatorInstance(
            $items,
            $total,
            self::PER_PAGE,
            $page,
            ['path' => $this->paginationPath($jobCard, $filter, $search), 'pageName' => 'timeline_page'],
        );
    }

    protected function emptyPaginator(int $page, ProductionJobCard $jobCard, string $filter, ?string $search): LengthAwarePaginator
    {
        return new PaginatorInstance(
            collect(),
            0,
            self::PER_PAGE,
            $page,
            ['path' => $this->paginationPath($jobCard, $filter, $search), 'pageName' => 'timeline_page'],
        );
    }

    protected function paginationPath(ProductionJobCard $jobCard, string $filter, ?string $search): string
    {
        return route('admin.production.job-cards.show', array_filter([
            'jobCard' => $jobCard,
            'tab' => Job360WorkspaceService::TAB_TIMELINE,
            'timeline_filter' => $filter !== self::FILTER_ALL ? $filter : null,
            'timeline_search' => $search,
        ]));
    }

    protected function sqlQuote(string $value): string
    {
        return "'".str_replace("'", "''", $value)."'";
    }

    protected function sqlConcat(string ...$parts): string
    {
        $driver = DB::connection()->getDriverName();

        return $driver === 'sqlite' ? implode(' || ', $parts) : 'CONCAT('.implode(', ', $parts).')';
    }

    public function usesDatabaseUnion(): bool
    {
        return true;
    }

    /**
     * @return list<\Closure(ProductionJobCard): Builder>
     */
    protected function deliveryNoteCollectors(): array
    {
        if (! Schema::hasTable('delivery_notes')) {
            return [];
        }

        return [
            fn (ProductionJobCard $j) => $this->deliveryNoteTimelineQuery($j),
        ];
    }

    protected function deliveryNoteTimelineQuery(ProductionJobCard $jobCard): Builder
    {
        $jobId = $jobCard->id;

        $created = DB::table('delivery_notes')
            ->where('delivery_notes.production_job_card_id', $jobId)
            ->whereNull('delivery_notes.deleted_at')
            ->select($this->eventSelect(
                eventType: 'DELIVERY_NOTE_CREATED',
                title: $this->sqlConcat($this->sqlQuote(__('Delivery note created').' '), 'delivery_notes.delivery_note_number'),
                description: $this->sqlQuote(__('Draft delivery note')),
                eventDatetime: 'delivery_notes.created_at',
                actorName: "'".__('System')."'",
                actorType: 'system',
                sourceType: 'delivery_note',
                sourceId: 'delivery_notes.id',
                sourceRoute: 'admin.dispatch.delivery-notes.show',
                sourceRouteParam: 'deliveryNote',
                icon: 'document-add',
                color: 'indigo',
                category: 'dispatch',
            ));

        $dispatched = DB::table('delivery_notes')
            ->leftJoin('users', 'users.id', '=', 'delivery_notes.dispatched_by')
            ->where('delivery_notes.production_job_card_id', $jobId)
            ->whereNotNull('delivery_notes.dispatched_at')
            ->whereNull('delivery_notes.deleted_at')
            ->select($this->eventSelect(
                eventType: 'DISPATCHED',
                title: $this->sqlConcat($this->sqlQuote(__('Dispatched').' '), 'delivery_notes.delivery_note_number'),
                description: 'COALESCE(delivery_notes.dispatch_notes, '."''".')',
                eventDatetime: 'delivery_notes.dispatched_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'delivery_note',
                sourceId: 'delivery_notes.id',
                sourceRoute: 'admin.dispatch.delivery-notes.show',
                sourceRouteParam: 'deliveryNote',
                icon: 'truck',
                color: 'sky',
                category: 'dispatch',
            ));

        $delivered = DB::table('delivery_notes')
            ->leftJoin('users', 'users.id', '=', 'delivery_notes.delivered_by')
            ->where('delivery_notes.production_job_card_id', $jobId)
            ->whereNotNull('delivery_notes.delivered_at')
            ->whereNull('delivery_notes.deleted_at')
            ->select($this->eventSelect(
                eventType: 'DELIVERED',
                title: $this->sqlConcat($this->sqlQuote(__('Delivered').' '), 'delivery_notes.delivery_note_number'),
                description: 'COALESCE(delivery_notes.delivery_notes, '."''".')',
                eventDatetime: 'delivery_notes.delivered_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'delivery_note',
                sourceId: 'delivery_notes.id',
                sourceRoute: 'admin.dispatch.delivery-notes.show',
                sourceRouteParam: 'deliveryNote',
                icon: 'check-circle',
                color: 'emerald',
                category: 'dispatch',
            ));

        $cancelled = DB::table('delivery_notes')
            ->where('delivery_notes.production_job_card_id', $jobId)
            ->where('delivery_notes.status', 'cancelled')
            ->whereNull('delivery_notes.deleted_at')
            ->select($this->eventSelect(
                eventType: 'CANCELLED',
                title: $this->sqlConcat($this->sqlQuote(__('Delivery note cancelled').' '), 'delivery_notes.delivery_note_number'),
                description: 'COALESCE(delivery_notes.delivery_notes, '."''".')',
                eventDatetime: 'delivery_notes.updated_at',
                actorName: "'".__('System')."'",
                actorType: 'system',
                sourceType: 'delivery_note',
                sourceId: 'delivery_notes.id',
                sourceRoute: 'admin.dispatch.delivery-notes.show',
                sourceRouteParam: 'deliveryNote',
                icon: 'ban',
                color: 'rose',
                category: 'dispatch',
            ));

        return $created->unionAll($dispatched)->unionAll($delivered)->unionAll($cancelled);
    }

    /**
     * Latest production events across the active tenant (command center feed).
     *
     * @return list<array<string, mixed>>
     */
    public function recentForTenant(int $limit = 20): array
    {
        $union = $this->buildTenantUnionQuery();

        if ($union === null) {
            return [];
        }

        $rows = DB::query()
            ->fromSub($union, 'production_timeline')
            ->where('event_type', '!=', 'SKIP')
            ->orderByDesc('event_datetime')
            ->orderByDesc('source_id')
            ->limit($limit)
            ->get();

        return $rows->map(function (object $row) {
            $event = JobTimelineEvent::fromRow($row);
            $payload = $event->toArray();
            $payload['job_number'] = $row->job_number ?? null;
            $payload['job_url'] = Route::has('admin.production.job-cards.show') && ! empty($row->job_card_id)
                ? route('admin.production.job-cards.show', ['jobCard' => $row->job_card_id])
                : null;

            return $payload;
        })->all();
    }

    protected function buildTenantUnionQuery(): ?Builder
    {
        $collectors = [
            $this->tenantJobCreatedQuery(),
            $this->tenantJobStartedQuery(),
            $this->tenantOperationsQuery(),
            $this->tenantQualityQuery(),
            $this->tenantQueueQuery(),
        ];

        if (Schema::hasTable('delivery_notes')) {
            $collectors[] = $this->tenantDeliveryQuery();
        }

        $union = null;

        foreach ($collectors as $query) {
            $union = $union === null ? $query : $union->unionAll($query);
        }

        return $union;
    }

    protected function tenantJobScope(Builder $query, string $jobTableAlias = 'production_job_cards'): Builder
    {
        if ($companyId = tenant()->companyId()) {
            $query->where("{$jobTableAlias}.company_id", $companyId);
        } else {
            $query->whereRaw('1 = 0');
        }

        if (tenant()->branchId()) {
            $query->where("{$jobTableAlias}.branch_id", tenant()->branchId());
        }

        return $query;
    }

    protected function tenantJobCreatedQuery(): Builder
    {
        return $this->tenantJobScope(
            DB::table('production_job_cards')
                ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
        )->select($this->tenantEventSelect(
            eventType: 'JOB_CREATED',
            title: $this->sqlQuote(__('Job card created')),
            description: 'production_job_cards.job_card_number',
            eventDatetime: 'production_job_cards.created_at',
            actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
            icon: 'document-add',
            color: 'slate',
            jobCardId: 'production_job_cards.id',
            jobNumber: 'production_job_cards.job_card_number',
        ));
    }

    protected function tenantJobStartedQuery(): Builder
    {
        return $this->tenantJobScope(
            DB::table('production_job_cards')
                ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
                ->whereNotNull('production_job_cards.actual_start_date')
        )->select($this->tenantEventSelect(
            eventType: 'JOB_STARTED',
            title: $this->sqlQuote(__('Production started')),
            description: 'production_job_cards.job_card_number',
            eventDatetime: 'production_job_cards.actual_start_date',
            actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
            icon: 'play',
            color: 'amber',
            jobCardId: 'production_job_cards.id',
            jobNumber: 'production_job_cards.job_card_number',
        ));
    }

    protected function tenantOperationsQuery(): Builder
    {
        $query = DB::table('production_operations')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_operations.production_job_card_id')
            ->leftJoin('work_centers', 'work_centers.id', '=', 'production_operations.work_center_id')
            ->whereNotNull('production_operations.started_at');

        return $this->tenantJobScope($query)->select($this->tenantEventSelect(
            eventType: 'OPERATION_STARTED',
            title: $this->sqlConcat($this->sqlQuote(__('Operation started').' — '), 'COALESCE(work_centers.name, '."''".')'),
            description: 'production_job_cards.job_card_number',
            eventDatetime: 'production_operations.started_at',
            actorName: "'".__('System')."'",
            icon: 'cog',
            color: 'sky',
            jobCardId: 'production_job_cards.id',
            jobNumber: 'production_job_cards.job_card_number',
        ));
    }

    protected function tenantQualityQuery(): Builder
    {
        $query = DB::table('quality_checks')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'quality_checks.production_job_card_id')
            ->leftJoin('users', 'users.id', '=', 'quality_checks.checked_by');

        return $this->tenantJobScope($query)->select($this->tenantEventSelect(
            eventType: 'QC_RECORDED',
            title: $this->sqlConcat($this->sqlQuote(__('Quality check').': '), 'quality_checks.result'),
            description: 'production_job_cards.job_card_number',
            eventDatetime: 'quality_checks.checked_at',
            actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
            icon: 'badge-check',
            color: 'indigo',
            jobCardId: 'production_job_cards.id',
            jobNumber: 'production_job_cards.job_card_number',
        ));
    }

    protected function tenantQueueQuery(): Builder
    {
        $query = DB::table('production_queues')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_queues.production_job_card_id')
            ->leftJoin('work_centers', 'work_centers.id', '=', 'production_queues.work_center_id');

        return $this->tenantJobScope($query)->select($this->tenantEventSelect(
            eventType: 'JOB_QUEUED',
            title: $this->sqlConcat($this->sqlQuote(__('Job queued').' — '), 'COALESCE(work_centers.name, '."''".')'),
            description: 'production_job_cards.job_card_number',
            eventDatetime: 'production_queues.created_at',
            actorName: "'".__('System')."'",
            icon: 'switch-horizontal',
            color: 'violet',
            jobCardId: 'production_job_cards.id',
            jobNumber: 'production_job_cards.job_card_number',
        ));
    }

    protected function tenantDeliveryQuery(): Builder
    {
        $query = DB::table('delivery_notes')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'delivery_notes.production_job_card_id')
            ->leftJoin('users', 'users.id', '=', 'delivery_notes.dispatched_by')
            ->whereNotNull('delivery_notes.dispatched_at')
            ->whereNull('delivery_notes.deleted_at');

        return $this->tenantJobScope($query)->select($this->tenantEventSelect(
            eventType: 'DISPATCHED',
            title: $this->sqlConcat($this->sqlQuote(__('Job dispatched').' — '), 'delivery_notes.delivery_note_number'),
            description: 'production_job_cards.job_card_number',
            eventDatetime: 'delivery_notes.dispatched_at',
            actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
            icon: 'truck',
            color: 'emerald',
            jobCardId: 'production_job_cards.id',
            jobNumber: 'production_job_cards.job_card_number',
        ));
    }

    /**
     * @return array<int, mixed>
     */
    protected function tenantEventSelect(
        string $eventType,
        string $title,
        string $description,
        string $eventDatetime,
        string $actorName,
        string $icon,
        string $color,
        string $jobCardId,
        string $jobNumber,
    ): array {
        return array_merge($this->eventSelect(
            eventType: $eventType,
            title: $title,
            description: $description,
            eventDatetime: $eventDatetime,
            actorName: $actorName,
            actorType: 'user',
            sourceType: 'job_card',
            sourceId: $jobCardId,
            sourceRoute: 'admin.production.job-cards.show',
            sourceRouteParam: 'jobCard',
            icon: $icon,
            color: $color,
            category: 'operations',
        ), [
            DB::raw("{$jobNumber} as job_number"),
            DB::raw("{$jobCardId} as job_card_id"),
        ]);
    }
}
