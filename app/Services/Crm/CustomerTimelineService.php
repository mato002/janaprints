<?php

namespace App\Services\Crm;

use App\DataTransferObjects\Crm\CustomerTimelineEvent;
use App\Enums\ArtworkApprovalDecision;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\CustomerFile;
use App\Models\Crm\CustomerNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator as PaginatorInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomerTimelineService
{
    public const PER_PAGE = 30;

    public const MAX_PER_PAGE = 50;

    public const FILTER_ALL = 'all';

    public const FILTER_NOTES = 'notes';

    public const FILTER_ACTIVITIES = 'activities';

    public const FILTER_FILES = 'files';

    public const FILTER_QUOTATIONS = 'quotations';

    public const FILTER_ORDERS = 'orders';

    public const FILTER_ARTWORK = 'artwork';

    public const FILTER_PRODUCTION = 'production';

    public const FILTER_QUALITY = 'quality';

    public const FILTER_DISPATCH = 'dispatch';

    public const FILTER_ACCOUNTING = 'accounting';

    /** @var list<string> */
    public const FILTERS = [
        self::FILTER_ALL,
        self::FILTER_NOTES,
        self::FILTER_ACTIVITIES,
        self::FILTER_FILES,
        self::FILTER_QUOTATIONS,
        self::FILTER_ORDERS,
        self::FILTER_ARTWORK,
        self::FILTER_PRODUCTION,
        self::FILTER_QUALITY,
        self::FILTER_DISPATCH,
        self::FILTER_ACCOUNTING,
    ];

    /**
     * @return array{
     *     events: LengthAwarePaginator,
     *     filter: string,
     *     search: string|null,
     *     filters: list<array{value: string, label: string}>,
     *     accounting_placeholder: bool,
     *     uses_union: bool,
     * }
     */
    public function paginate(Customer $customer, ?string $filter = null, ?string $search = null, ?int $page = null): array
    {
        $filter = $this->resolveFilter($filter);
        $search = $this->normalizeSearch($search);
        $page = max(1, (int) ($page ?? 1));

        $collectors = $this->collectorsForFilter($filter, $customer);
        $union = $this->buildUnionQuery($collectors, $customer);

        if ($union === null) {
            return [
                'events' => $this->emptyPaginator($page, $customer, $filter, $search),
                'filter' => $filter,
                'search' => $search,
                'filters' => $this->filterOptions(),
                'accounting_placeholder' => false,
                'uses_union' => false,
            ];
        }

        $wrapped = DB::query()->fromSub($union, 'customer_timeline');

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

        $events = $this->mapRowsToPaginator($rows, $total, $page, $customer, $filter, $search);

        return [
            'events' => $events,
            'filter' => $filter,
            'search' => $search,
            'filters' => $this->filterOptions(),
            'accounting_placeholder' => false,
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

        if ($search === '' || strlen($search) < 2) {
            return null;
        }

        return Str::limit($search, 100, '');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function filterOptions(): array
    {
        return [
            ['value' => self::FILTER_ALL, 'label' => __('All')],
            ['value' => self::FILTER_NOTES, 'label' => __('Notes')],
            ['value' => self::FILTER_ACTIVITIES, 'label' => __('Activities')],
            ['value' => self::FILTER_FILES, 'label' => __('Files')],
            ['value' => self::FILTER_QUOTATIONS, 'label' => __('Quotations')],
            ['value' => self::FILTER_ORDERS, 'label' => __('Orders')],
            ['value' => self::FILTER_ARTWORK, 'label' => __('Artwork')],
            ['value' => self::FILTER_PRODUCTION, 'label' => __('Production')],
            ['value' => self::FILTER_QUALITY, 'label' => __('Quality')],
            ['value' => self::FILTER_DISPATCH, 'label' => __('Dispatch')],
            ['value' => self::FILTER_ACCOUNTING, 'label' => __('Accounting')],
        ];
    }

    /**
     * @return list<\Closure(Customer): Builder>
     */
    protected function collectorsForFilter(string $filter, Customer $customer): array
    {
        return match ($filter) {
            self::FILTER_NOTES => [
                fn (Customer $c) => $this->notesQuery($c),
            ],
            self::FILTER_ACTIVITIES => [
                fn (Customer $c) => $this->customerActivitiesQuery($c),
            ],
            self::FILTER_FILES => [
                fn (Customer $c) => $this->filesQuery($c),
            ],
            self::FILTER_QUOTATIONS => [
                fn (Customer $c) => $this->quotationsQuery($c),
            ],
            self::FILTER_ORDERS => [
                fn (Customer $c) => $this->salesOrdersQuery($c),
            ],
            self::FILTER_ARTWORK => [
                fn (Customer $c) => $this->artworkRequestsQuery($c),
                fn (Customer $c) => $this->artworkApprovalsQuery($c),
            ],
            self::FILTER_PRODUCTION => [
                fn (Customer $c) => $this->productionJobsQuery($c),
            ],
            self::FILTER_QUALITY => [
                fn (Customer $c) => $this->qualityChecksQuery($c),
            ],
            self::FILTER_DISPATCH => array_merge(
                [fn (Customer $c) => $this->dispatchEventsQuery($c)],
                $this->deliveryNoteCollectors(),
                $this->fulfilmentCollectors(),
            ),
            self::FILTER_ACCOUNTING => $this->accountingCollectors(),
            default => array_merge([
                fn (Customer $c) => $this->customerCreatedQuery($c),
                fn (Customer $c) => $this->notesQuery($c),
                fn (Customer $c) => $this->customerActivitiesQuery($c),
                fn (Customer $c) => $this->activityLogsQuery($c),
                fn (Customer $c) => $this->filesQuery($c),
                fn (Customer $c) => $this->quotationsQuery($c),
                fn (Customer $c) => $this->salesOrdersQuery($c),
                fn (Customer $c) => $this->artworkRequestsQuery($c),
                fn (Customer $c) => $this->artworkApprovalsQuery($c),
                fn (Customer $c) => $this->productionJobsQuery($c),
                fn (Customer $c) => $this->qualityChecksQuery($c),
                fn (Customer $c) => $this->dispatchEventsQuery($c),
            ], $this->deliveryNoteCollectors(), $this->fulfilmentCollectors(), $this->accountingCollectors()),
        };
    }

    /**
     * @param  list<\Closure(Customer): Builder>  $collectors
     */
    protected function buildUnionQuery(array $collectors, Customer $customer): ?Builder
    {
        $union = null;

        foreach ($collectors as $collector) {
            $query = $collector($customer);
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

    protected function customerCreatedQuery(Customer $customer): Builder
    {
        return DB::table('customers')
            ->where('customers.id', $customer->id)
            ->select($this->eventSelect(
                eventType: 'CUSTOMER_CREATED',
                title: $this->sqlQuote(__('Customer record created')),
                description: $this->sqlConcat('customers.company_name'),
                eventDatetime: 'customers.created_at',
                actorName: "'".__('System')."'",
                actorType: 'system',
                sourceType: 'customer',
                sourceId: 'customers.id',
                sourceRoute: 'admin.crm.customers.show',
                sourceRouteParam: 'customer',
                icon: 'user-group',
                color: 'slate',
                category: 'activities',
                metadata: $this->jsonObject(['code' => 'customers.customer_code']),
            ));
    }

    protected function notesQuery(Customer $customer): Builder
    {
        return DB::table('customer_notes')
            ->leftJoin('users', 'users.id', '=', 'customer_notes.user_id')
            ->where('customer_notes.customer_id', $customer->id)
            ->where('customer_notes.company_id', $customer->company_id)
            ->select($this->eventSelect(
                eventType: 'NOTE_ADDED',
                title: $this->sqlQuote(__('Note added')),
                description: $this->sqlSubstr('customer_notes.note', 500),
                eventDatetime: 'customer_notes.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'customer_note',
                sourceId: 'customer_notes.customer_id',
                sourceRoute: 'admin.crm.customers.show',
                sourceRouteParam: 'customer',
                icon: 'document-text',
                color: 'blue',
                category: 'notes',
                metadata: $this->jsonObject(['tab' => "'notes'", 'note_id' => 'customer_notes.id']),
            ));
    }

    protected function customerActivitiesQuery(Customer $customer): Builder
    {
        return DB::table('customer_activities')
            ->leftJoin('users', 'users.id', '=', 'customer_activities.user_id')
            ->where('customer_activities.customer_id', $customer->id)
            ->where('customer_activities.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('customer_activities.branch_id', $customer->branch_id))
            ->select($this->eventSelect(
                eventType: 'ACTIVITY_LOGGED',
                title: $this->sqlConcat($this->sqlQuote(__('Activity').': '), 'customer_activities.activity_type'),
                description: 'COALESCE(customer_activities.description, customer_activities.subject)',
                eventDatetime: 'customer_activities.activity_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'customer_activity',
                sourceId: 'customer_activities.customer_id',
                sourceRoute: 'admin.crm.customers.show',
                sourceRouteParam: 'customer',
                icon: 'phone',
                color: 'indigo',
                category: 'activities',
                metadata: $this->jsonObject([
                    'tab' => "'activities'",
                    'subject' => 'customer_activities.subject',
                    'activity_id' => 'customer_activities.id',
                ]),
            ));
    }

    protected function activityLogsQuery(Customer $customer): Builder
    {
        return DB::table('activity_logs')
            ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')
            ->where('activity_logs.company_id', $customer->company_id)
            ->where(function (Builder $query) use ($customer) {
                $query->where(function (Builder $q) use ($customer) {
                    $q->where('activity_logs.model_type', Customer::class)
                        ->where('activity_logs.model_id', $customer->id);
                })->orWhere(function (Builder $q) use ($customer) {
                    $q->where('activity_logs.model_type', CustomerActivity::class)
                        ->whereIn('activity_logs.model_id', CustomerActivity::query()
                            ->where('customer_id', $customer->id)
                            ->select('id'));
                })->orWhere(function (Builder $q) use ($customer) {
                    $q->where('activity_logs.model_type', Quotation::class)
                        ->whereIn('activity_logs.model_id', Quotation::query()
                            ->where('customer_id', $customer->id)
                            ->where('company_id', $customer->company_id)
                            ->select('id'));
                })->orWhere(function (Builder $q) use ($customer) {
                    $q->where('activity_logs.model_type', SalesOrder::class)
                        ->whereIn('activity_logs.model_id', SalesOrder::query()
                            ->where('customer_id', $customer->id)
                            ->where('company_id', $customer->company_id)
                            ->select('id'));
                })->orWhere(function (Builder $q) use ($customer) {
                    $q->where('activity_logs.model_type', ArtworkRequest::class)
                        ->whereIn('activity_logs.model_id', ArtworkRequest::query()
                            ->where('customer_id', $customer->id)
                            ->where('company_id', $customer->company_id)
                            ->select('id'));
                })->orWhere(function (Builder $q) use ($customer) {
                    $q->where('activity_logs.model_type', ProductionJobCard::class)
                        ->whereIn('activity_logs.model_id', ProductionJobCard::query()
                            ->where('customer_id', $customer->id)
                            ->where('company_id', $customer->company_id)
                            ->select('id'));
                });
            })
            ->select($this->eventSelect(
                eventType: 'SYSTEM_LOG',
                title: $this->sqlConcat($this->sqlQuote(__('System').': '), 'activity_logs.action'),
                description: $this->sqlConcat('activity_logs.model_type', $this->sqlQuote(' #'), 'activity_logs.model_id'),
                eventDatetime: 'activity_logs.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'activity_log',
                sourceId: 'activity_logs.id',
                sourceRoute: null,
                sourceRouteParam: 'id',
                icon: 'shield-check',
                color: 'slate',
                category: 'activities',
                metadata: $this->jsonObject(['action' => 'activity_logs.action']),
            ));
    }

    protected function filesQuery(Customer $customer): Builder
    {
        return DB::table('customer_files')
            ->leftJoin('users', 'users.id', '=', 'customer_files.uploaded_by')
            ->where('customer_files.customer_id', $customer->id)
            ->where('customer_files.company_id', $customer->company_id)
            ->select($this->eventSelect(
                eventType: 'FILE_UPLOADED',
                title: $this->sqlQuote(__('File uploaded')),
                description: 'customer_files.original_name',
                eventDatetime: 'customer_files.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'customer_file',
                sourceId: 'customer_files.customer_id',
                sourceRoute: 'admin.crm.customers.show',
                sourceRouteParam: 'customer',
                icon: 'paper-clip',
                color: 'amber',
                category: 'files',
                metadata: $this->jsonObject([
                    'tab' => "'files'",
                    'file_id' => 'customer_files.id',
                    'mime_type' => 'customer_files.mime_type',
                    'size' => 'customer_files.size',
                ]),
            ));
    }

    protected function quotationsQuery(Customer $customer): Builder
    {
        $created = DB::table('quotations')
            ->leftJoin('users', 'users.id', '=', 'quotations.prepared_by')
            ->where('quotations.customer_id', $customer->id)
            ->where('quotations.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('quotations.branch_id', $customer->branch_id))
            ->select($this->eventSelect(
                eventType: 'QUOTATION_CREATED',
                title: $this->sqlConcat($this->sqlQuote(__('Quotation').' '), 'quotations.quotation_number', $this->sqlQuote(' '.__('created'))),
                description: $this->sqlConcat($this->sqlQuote(__('Status').': '), 'quotations.status'),
                eventDatetime: 'quotations.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'quotation',
                sourceId: 'quotations.id',
                sourceRoute: 'admin.quotations.show',
                sourceRouteParam: 'quotation',
                icon: 'document-text',
                color: 'sky',
                category: 'quotations',
                metadata: $this->jsonObject(['number' => 'quotations.quotation_number']),
            ));

        $approved = DB::table('quotations')
            ->leftJoin('users', 'users.id', '=', 'quotations.approved_by')
            ->where('quotations.customer_id', $customer->id)
            ->where('quotations.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('quotations.branch_id', $customer->branch_id))
            ->whereNotNull('quotations.approved_at')
            ->select($this->eventSelect(
                eventType: 'QUOTATION_APPROVED',
                title: $this->sqlConcat($this->sqlQuote(__('Quotation').' '), 'quotations.quotation_number', $this->sqlQuote(' '.__('approved'))),
                description: $this->sqlQuote(__('Quotation approved')),
                eventDatetime: 'quotations.approved_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'quotation',
                sourceId: 'quotations.id',
                sourceRoute: 'admin.quotations.show',
                sourceRouteParam: 'quotation',
                icon: 'check-circle',
                color: 'emerald',
                category: 'quotations',
            ));

        $rejected = DB::table('quotations')
            ->leftJoin('users', 'users.id', '=', 'quotations.prepared_by')
            ->where('quotations.customer_id', $customer->id)
            ->where('quotations.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('quotations.branch_id', $customer->branch_id))
            ->where('quotations.status', QuotationStatus::Rejected->value)
            ->select($this->eventSelect(
                eventType: 'QUOTATION_REJECTED',
                title: $this->sqlConcat($this->sqlQuote(__('Quotation').' '), 'quotations.quotation_number', $this->sqlQuote(' '.__('rejected'))),
                description: $this->sqlQuote(__('Quotation rejected')),
                eventDatetime: 'quotations.updated_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'quotation',
                sourceId: 'quotations.id',
                sourceRoute: 'admin.quotations.show',
                sourceRouteParam: 'quotation',
                icon: 'x-circle',
                color: 'red',
                category: 'quotations',
            ));

        return $created->unionAll($approved)->unionAll($rejected);
    }

    protected function salesOrdersQuery(Customer $customer): Builder
    {
        $created = DB::table('sales_orders')
            ->leftJoin('users', 'users.id', '=', 'sales_orders.created_by')
            ->where('sales_orders.customer_id', $customer->id)
            ->where('sales_orders.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('sales_orders.branch_id', $customer->branch_id))
            ->select($this->eventSelect(
                eventType: 'SALES_ORDER_CREATED',
                title: $this->sqlConcat($this->sqlQuote(__('Sales order').' '), 'sales_orders.order_number', $this->sqlQuote(' '.__('created'))),
                description: $this->sqlConcat($this->sqlQuote(__('Status').': '), 'sales_orders.status'),
                eventDatetime: 'sales_orders.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'sales_order',
                sourceId: 'sales_orders.id',
                sourceRoute: 'admin.sales-orders.show',
                sourceRouteParam: 'salesOrder',
                icon: 'shopping-cart',
                color: 'violet',
                category: 'orders',
            ));

        $delivered = DB::table('sales_orders')
            ->leftJoin('users', 'users.id', '=', 'sales_orders.created_by')
            ->where('sales_orders.customer_id', $customer->id)
            ->where('sales_orders.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('sales_orders.branch_id', $customer->branch_id))
            ->whereIn('sales_orders.status', [SalesOrderStatus::Delivered->value, SalesOrderStatus::Closed->value])
            ->select($this->eventSelect(
                eventType: 'DELIVERED',
                title: $this->sqlConcat($this->sqlQuote(__('Order').' '), 'sales_orders.order_number', $this->sqlQuote(' '.__('delivered'))),
                description: $this->sqlQuote(__('Order marked as delivered')),
                eventDatetime: 'sales_orders.updated_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'sales_order',
                sourceId: 'sales_orders.id',
                sourceRoute: 'admin.sales-orders.show',
                sourceRouteParam: 'salesOrder',
                icon: 'truck',
                color: 'emerald',
                category: 'dispatch',
            ));

        return $created->unionAll($delivered);
    }

    protected function artworkRequestsQuery(Customer $customer): Builder
    {
        return DB::table('artwork_requests')
            ->leftJoin('users', 'users.id', '=', 'artwork_requests.requested_by')
            ->where('artwork_requests.customer_id', $customer->id)
            ->where('artwork_requests.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('artwork_requests.branch_id', $customer->branch_id))
            ->select($this->eventSelect(
                eventType: 'ARTWORK_REQUESTED',
                title: $this->sqlConcat($this->sqlQuote(__('Artwork').' '), 'artwork_requests.request_number', $this->sqlQuote(' '.__('requested'))),
                description: 'artwork_requests.title',
                eventDatetime: 'artwork_requests.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'artwork_request',
                sourceId: 'artwork_requests.id',
                sourceRoute: 'admin.artwork.show',
                sourceRouteParam: 'artworkRequest',
                icon: 'color-swatch',
                color: 'pink',
                category: 'artwork',
            ));
    }

    protected function artworkApprovalsQuery(Customer $customer): Builder
    {
        $approved = DB::table('artwork_approvals')
            ->join('artwork_requests', 'artwork_requests.id', '=', 'artwork_approvals.artwork_request_id')
            ->leftJoin('users', 'users.id', '=', 'artwork_approvals.approved_by')
            ->where('artwork_requests.customer_id', $customer->id)
            ->where('artwork_approvals.company_id', $customer->company_id)
            ->where('artwork_approvals.decision', ArtworkApprovalDecision::Approved->value)
            ->select($this->eventSelect(
                eventType: 'ARTWORK_APPROVED',
                title: $this->sqlConcat($this->sqlQuote(__('Artwork').' '), 'artwork_requests.request_number', $this->sqlQuote(' '.__('approved'))),
                description: 'COALESCE(artwork_approvals.comments, artwork_requests.title)',
                eventDatetime: 'artwork_approvals.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'artwork_request',
                sourceId: 'artwork_requests.id',
                sourceRoute: 'admin.artwork.show',
                sourceRouteParam: 'artworkRequest',
                icon: 'check-circle',
                color: 'emerald',
                category: 'artwork',
            ));

        $rejected = DB::table('artwork_approvals')
            ->join('artwork_requests', 'artwork_requests.id', '=', 'artwork_approvals.artwork_request_id')
            ->leftJoin('users', 'users.id', '=', 'artwork_approvals.approved_by')
            ->where('artwork_requests.customer_id', $customer->id)
            ->where('artwork_approvals.company_id', $customer->company_id)
            ->where('artwork_approvals.decision', ArtworkApprovalDecision::Rejected->value)
            ->select($this->eventSelect(
                eventType: 'ARTWORK_REJECTED',
                title: $this->sqlConcat($this->sqlQuote(__('Artwork').' '), 'artwork_requests.request_number', $this->sqlQuote(' '.__('rejected'))),
                description: 'COALESCE(artwork_approvals.comments, artwork_requests.title)',
                eventDatetime: 'artwork_approvals.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'artwork_request',
                sourceId: 'artwork_requests.id',
                sourceRoute: 'admin.artwork.show',
                sourceRouteParam: 'artworkRequest',
                icon: 'x-circle',
                color: 'red',
                category: 'artwork',
            ));

        return $approved->unionAll($rejected);
    }

    protected function productionJobsQuery(Customer $customer): Builder
    {
        $created = DB::table('production_job_cards')
            ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('production_job_cards.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('production_job_cards.branch_id', $customer->branch_id))
            ->select($this->eventSelect(
                eventType: 'JOB_CREATED',
                title: $this->sqlConcat($this->sqlQuote(__('Job').' '), 'production_job_cards.job_card_number', $this->sqlQuote(' '.__('created'))),
                description: $this->sqlConcat($this->sqlQuote(__('Status').': '), 'production_job_cards.status'),
                eventDatetime: 'production_job_cards.created_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'production_job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'cog',
                color: 'orange',
                category: 'production',
            ));

        $started = DB::table('production_job_cards')
            ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('production_job_cards.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('production_job_cards.branch_id', $customer->branch_id))
            ->whereNotNull('production_job_cards.actual_start_date')
            ->select($this->eventSelect(
                eventType: 'JOB_STARTED',
                title: $this->sqlConcat($this->sqlQuote(__('Job').' '), 'production_job_cards.job_card_number', $this->sqlQuote(' '.__('started'))),
                description: $this->sqlQuote(__('Production started')),
                eventDatetime: 'production_job_cards.actual_start_date',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'production_job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'play',
                color: 'amber',
                category: 'production',
            ));

        $completed = DB::table('production_job_cards')
            ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('production_job_cards.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('production_job_cards.branch_id', $customer->branch_id))
            ->whereIn('production_job_cards.status', [
                ProductionJobCardStatus::Completed->value,
                ProductionJobCardStatus::ReadyForDispatch->value,
            ])
            ->select($this->eventSelect(
                eventType: 'JOB_COMPLETED',
                title: $this->sqlConcat($this->sqlQuote(__('Job').' '), 'production_job_cards.job_card_number', $this->sqlQuote(' '.__('completed'))),
                description: $this->sqlQuote(__('Production job completed')),
                eventDatetime: 'COALESCE(production_job_cards.actual_end_date, production_job_cards.updated_at)',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'production_job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'check-circle',
                color: 'emerald',
                category: 'production',
            ));

        return $created->unionAll($started)->unionAll($completed);
    }

    protected function qualityChecksQuery(Customer $customer): Builder
    {
        $passed = DB::table('quality_checks')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'quality_checks.production_job_card_id')
            ->leftJoin('users', 'users.id', '=', 'quality_checks.checked_by')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('quality_checks.company_id', $customer->company_id)
            ->where('quality_checks.result', QualityCheckResult::Passed->value)
            ->select($this->eventSelect(
                eventType: 'QC_PASSED',
                title: $this->sqlConcat($this->sqlQuote(__('QC passed').' — '), 'production_job_cards.job_card_number'),
                description: 'COALESCE(quality_checks.comments, '."''".')',
                eventDatetime: 'quality_checks.checked_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'production_job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'badge-check',
                color: 'emerald',
                category: 'quality',
            ));

        $failed = DB::table('quality_checks')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'quality_checks.production_job_card_id')
            ->leftJoin('users', 'users.id', '=', 'quality_checks.checked_by')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('quality_checks.company_id', $customer->company_id)
            ->whereIn('quality_checks.result', [
                QualityCheckResult::Failed->value,
                QualityCheckResult::ReworkRequired->value,
            ])
            ->select($this->eventSelect(
                eventType: 'QC_FAILED',
                title: $this->sqlConcat($this->sqlQuote(__('QC failed').' — '), 'production_job_cards.job_card_number'),
                description: 'COALESCE(quality_checks.comments, '."''".')',
                eventDatetime: 'quality_checks.checked_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'production_job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'exclamation',
                color: 'red',
                category: 'quality',
            ));

        return $passed->unionAll($failed);
    }

    protected function dispatchEventsQuery(Customer $customer): Builder
    {
        $dispatched = DB::table('production_job_cards')
            ->leftJoin('users', 'users.id', '=', 'production_job_cards.created_by')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('production_job_cards.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('production_job_cards.branch_id', $customer->branch_id))
            ->where('production_job_cards.status', ProductionJobCardStatus::ReadyForDispatch->value)
            ->select($this->eventSelect(
                eventType: 'DISPATCHED',
                title: $this->sqlConcat($this->sqlQuote(__('Job').' '), 'production_job_cards.job_card_number', $this->sqlQuote(' '.__('ready for dispatch'))),
                description: $this->sqlQuote(__('Job ready for dispatch')),
                eventDatetime: 'production_job_cards.updated_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'production_job_card',
                sourceId: 'production_job_cards.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'truck',
                color: 'sky',
                category: 'dispatch',
            ));

        $delivered = DB::table('sales_orders')
            ->leftJoin('users', 'users.id', '=', 'sales_orders.created_by')
            ->where('sales_orders.customer_id', $customer->id)
            ->where('sales_orders.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('sales_orders.branch_id', $customer->branch_id))
            ->whereIn('sales_orders.status', [SalesOrderStatus::Delivered->value, SalesOrderStatus::Closed->value])
            ->select($this->eventSelect(
                eventType: 'DELIVERED',
                title: $this->sqlConcat($this->sqlQuote(__('Order').' '), 'sales_orders.order_number', $this->sqlQuote(' '.__('delivered'))),
                description: $this->sqlQuote(__('Order delivered to customer')),
                eventDatetime: 'sales_orders.updated_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'sales_order',
                sourceId: 'sales_orders.id',
                sourceRoute: 'admin.sales-orders.show',
                sourceRouteParam: 'salesOrder',
                icon: 'truck',
                color: 'emerald',
                category: 'dispatch',
            ));

        return $dispatched->unionAll($delivered);
    }

    /**
     * @return list<\Closure(Customer): Builder>
     */
    protected function deliveryNoteCollectors(): array
    {
        if (! Schema::hasTable('delivery_notes')) {
            return [];
        }

        return [
            fn (Customer $c) => $this->deliveryNoteTimelineQuery($c),
        ];
    }

    protected function deliveryNoteTimelineQuery(Customer $customer): Builder
    {
        $created = DB::table('delivery_notes')
            ->where('delivery_notes.customer_id', $customer->id)
            ->where('delivery_notes.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('delivery_notes.branch_id', $customer->branch_id))
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
            ->where('delivery_notes.customer_id', $customer->id)
            ->where('delivery_notes.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('delivery_notes.branch_id', $customer->branch_id))
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
            ->where('delivery_notes.customer_id', $customer->id)
            ->where('delivery_notes.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('delivery_notes.branch_id', $customer->branch_id))
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
            ->leftJoin('users', 'users.id', '=', 'delivery_notes.dispatched_by')
            ->where('delivery_notes.customer_id', $customer->id)
            ->where('delivery_notes.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('delivery_notes.branch_id', $customer->branch_id))
            ->where('delivery_notes.status', 'cancelled')
            ->whereNull('delivery_notes.deleted_at')
            ->select($this->eventSelect(
                eventType: 'CANCELLED',
                title: $this->sqlConcat($this->sqlQuote(__('Delivery note cancelled').' '), 'delivery_notes.delivery_note_number'),
                description: 'COALESCE(delivery_notes.delivery_notes, '."''".')',
                eventDatetime: 'delivery_notes.updated_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
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
     * @return list<\Closure(Customer): Builder>
     */
    protected function fulfilmentCollectors(): array
    {
        if (! Schema::hasTable('production_fulfilments')) {
            return [];
        }

        return [
            fn (Customer $c) => $this->fulfilmentHistoryQuery($c),
        ];
    }

    protected function fulfilmentHistoryQuery(Customer $customer): Builder
    {
        $ready = DB::table('production_fulfilments')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_fulfilments.production_job_card_id')
            ->leftJoin('sales_orders', 'sales_orders.id', '=', 'production_fulfilments.sales_order_id')
            ->leftJoin('users', 'users.id', '=', 'production_fulfilments.prepared_by')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('production_fulfilments.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('production_fulfilments.branch_id', $customer->branch_id))
            ->whereNotNull('production_fulfilments.prepared_at')
            ->select($this->eventSelect(
                eventType: 'READY_FOR_COLLECTION',
                title: $this->sqlConcat($this->sqlQuote(__('Order').' '), 'sales_orders.order_number', $this->sqlQuote(' '.__('ready for collection'))),
                description: 'COALESCE(production_fulfilments.collection_notes, '."''".')',
                eventDatetime: 'production_fulfilments.prepared_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'production_fulfilment',
                sourceId: 'production_fulfilments.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'shopping-bag',
                color: 'sky',
                category: 'dispatch',
                metadata: $this->jsonObject([
                    'order' => 'sales_orders.order_number',
                    'job_card_id' => 'production_job_cards.id',
                ]),
            ));

        $collected = DB::table('production_fulfilments')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_fulfilments.production_job_card_id')
            ->leftJoin('sales_orders', 'sales_orders.id', '=', 'production_fulfilments.sales_order_id')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('production_fulfilments.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('production_fulfilments.branch_id', $customer->branch_id))
            ->where('production_fulfilments.status', 'collected')
            ->whereNotNull('production_fulfilments.collected_at')
            ->select($this->eventSelect(
                eventType: 'COLLECTED',
                title: $this->sqlConcat($this->sqlQuote(__('Order').' '), 'sales_orders.order_number', $this->sqlQuote(' '.__('collected'))),
                description: 'COALESCE(production_fulfilments.collected_by_name, '."''".')',
                eventDatetime: 'production_fulfilments.collected_at',
                actorName: 'COALESCE(production_fulfilments.collected_by_name, '."'".__('Customer')."'".')',
                actorType: 'customer',
                sourceType: 'production_fulfilment',
                sourceId: 'production_fulfilments.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'check-circle',
                color: 'emerald',
                category: 'dispatch',
                metadata: $this->jsonObject([
                    'order' => 'sales_orders.order_number',
                    'collection_date' => 'production_fulfilments.collected_at',
                    'recipient' => 'production_fulfilments.collected_by_name',
                ]),
            ));

        $delivered = DB::table('production_fulfilments')
            ->join('production_job_cards', 'production_job_cards.id', '=', 'production_fulfilments.production_job_card_id')
            ->leftJoin('sales_orders', 'sales_orders.id', '=', 'production_fulfilments.sales_order_id')
            ->where('production_job_cards.customer_id', $customer->id)
            ->where('production_fulfilments.company_id', $customer->company_id)
            ->when($customer->branch_id, fn (Builder $q) => $q->where('production_fulfilments.branch_id', $customer->branch_id))
            ->where('production_fulfilments.status', 'delivered')
            ->whereNotNull('production_fulfilments.delivered_at')
            ->select($this->eventSelect(
                eventType: 'FULFILMENT_DELIVERED',
                title: $this->sqlConcat($this->sqlQuote(__('Order').' '), 'sales_orders.order_number', $this->sqlQuote(' '.__('delivered'))),
                description: 'COALESCE(production_fulfilments.received_by, production_fulfilments.recipient_name, '."''".')',
                eventDatetime: 'production_fulfilments.delivered_at',
                actorName: 'COALESCE(production_fulfilments.received_by, '."'".__('Customer')."'".')',
                actorType: 'customer',
                sourceType: 'production_fulfilment',
                sourceId: 'production_fulfilments.id',
                sourceRoute: 'admin.production.job-cards.show',
                sourceRouteParam: 'jobCard',
                icon: 'truck',
                color: 'emerald',
                category: 'dispatch',
                metadata: $this->jsonObject([
                    'order' => 'sales_orders.order_number',
                    'delivery_date' => 'production_fulfilments.delivered_at',
                    'recipient' => 'COALESCE(production_fulfilments.received_by, production_fulfilments.recipient_name)',
                ]),
            ));

        return $ready->unionAll($collected)->unionAll($delivered);
    }

    /**
     * @return list<\Closure(Customer): Builder>
     */
    protected function accountingCollectors(): array
    {
        if (! Schema::hasTable('invoices')) {
            return [];
        }

        $collectors = [
            fn (Customer $c) => $this->invoicesIssuedQuery($c),
            fn (Customer $c) => $this->invoicesVoidedQuery($c),
        ];

        if (Schema::hasTable('payments')) {
            $collectors[] = fn (Customer $c) => $this->paymentsPostedQuery($c);
            $collectors[] = fn (Customer $c) => $this->paymentsReversedQuery($c);
            $collectors[] = fn (Customer $c) => $this->paymentAllocationsQuery($c);
        }

        return $collectors;
    }

    protected function invoicesIssuedQuery(Customer $customer): Builder
    {
        return DB::table('invoices')
            ->leftJoin('users', 'users.id', '=', 'invoices.issued_by')
            ->where('invoices.customer_id', $customer->id)
            ->where('invoices.company_id', $customer->company_id)
            ->whereNotNull('invoices.issued_at')
            ->whereNull('invoices.deleted_at')
            ->select($this->eventSelect(
                eventType: 'INVOICE_ISSUED',
                title: $this->sqlConcat($this->sqlQuote(__('Invoice issued').' '), 'invoices.invoice_number'),
                description: $this->sqlConcat($this->sqlQuote(__('Amount').': '), 'invoices.total_amount'),
                eventDatetime: 'invoices.issued_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'invoice',
                sourceId: 'invoices.id',
                sourceRoute: 'admin.accounting.invoices.show',
                sourceRouteParam: 'invoice',
                icon: 'receipt-tax',
                color: 'indigo',
                category: 'accounting',
                metadata: $this->jsonObject(['number' => 'invoices.invoice_number']),
            ));
    }

    protected function invoicesVoidedQuery(Customer $customer): Builder
    {
        return DB::table('invoices')
            ->leftJoin('users', 'users.id', '=', 'invoices.voided_by')
            ->where('invoices.customer_id', $customer->id)
            ->where('invoices.company_id', $customer->company_id)
            ->whereNotNull('invoices.voided_at')
            ->whereNull('invoices.deleted_at')
            ->select($this->eventSelect(
                eventType: 'INVOICE_VOIDED',
                title: $this->sqlConcat($this->sqlQuote(__('Invoice voided').' '), 'invoices.invoice_number'),
                description: 'invoices.void_reason',
                eventDatetime: 'invoices.voided_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'invoice',
                sourceId: 'invoices.id',
                sourceRoute: 'admin.accounting.invoices.show',
                sourceRouteParam: 'invoice',
                icon: 'ban',
                color: 'rose',
                category: 'accounting',
            ));
    }

    protected function paymentsPostedQuery(Customer $customer): Builder
    {
        return DB::table('payments')
            ->leftJoin('users', 'users.id', '=', 'payments.posted_by')
            ->where('payments.customer_id', $customer->id)
            ->where('payments.company_id', $customer->company_id)
            ->whereNotNull('payments.posted_at')
            ->whereNull('payments.deleted_at')
            ->select($this->eventSelect(
                eventType: 'PAYMENT_RECEIVED',
                title: $this->sqlConcat($this->sqlQuote(__('Payment received').' '), 'payments.payment_number'),
                description: $this->sqlConcat($this->sqlQuote(__('Amount').': '), 'payments.amount'),
                eventDatetime: 'payments.posted_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'payment',
                sourceId: 'payments.id',
                sourceRoute: 'admin.accounting.payments.show',
                sourceRouteParam: 'payment',
                icon: 'credit-card',
                color: 'emerald',
                category: 'accounting',
            ));
    }

    protected function paymentsReversedQuery(Customer $customer): Builder
    {
        return DB::table('payments')
            ->leftJoin('users', 'users.id', '=', 'payments.reversed_by')
            ->where('payments.customer_id', $customer->id)
            ->where('payments.company_id', $customer->company_id)
            ->whereNotNull('payments.reversed_at')
            ->whereNull('payments.deleted_at')
            ->select($this->eventSelect(
                eventType: 'PAYMENT_REVERSED',
                title: $this->sqlConcat($this->sqlQuote(__('Payment reversed').' '), 'payments.payment_number'),
                description: 'payments.reversal_reason',
                eventDatetime: 'payments.reversed_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'payment',
                sourceId: 'payments.id',
                sourceRoute: 'admin.accounting.payments.show',
                sourceRouteParam: 'payment',
                icon: 'arrow-uturn-left',
                color: 'amber',
                category: 'accounting',
            ));
    }

    protected function paymentAllocationsQuery(Customer $customer): Builder
    {
        return DB::table('payment_allocations')
            ->join('payments', 'payments.id', '=', 'payment_allocations.payment_id')
            ->join('invoices', 'invoices.id', '=', 'payment_allocations.invoice_id')
            ->leftJoin('users', 'users.id', '=', 'payment_allocations.allocated_by')
            ->where('payments.customer_id', $customer->id)
            ->where('payments.company_id', $customer->company_id)
            ->select($this->eventSelect(
                eventType: 'PAYMENT_ALLOCATED',
                title: $this->sqlConcat(
                    $this->sqlQuote(__('Payment allocated').' '),
                    'payments.payment_number',
                    $this->sqlQuote(' → '),
                    'invoices.invoice_number',
                ),
                description: $this->sqlConcat($this->sqlQuote(__('Amount').': '), 'payment_allocations.allocated_amount'),
                eventDatetime: 'payment_allocations.allocated_at',
                actorName: 'COALESCE(users.name, '."'".__('System')."'".')',
                actorType: 'user',
                sourceType: 'payment_allocation',
                sourceId: 'payment_allocations.id',
                sourceRoute: 'admin.accounting.payments.show',
                sourceRouteParam: 'payment',
                icon: 'switch-horizontal',
                color: 'sky',
                category: 'accounting',
            ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     */
    protected function mapRowsToPaginator(
        $rows,
        int $total,
        int $page,
        Customer $customer,
        string $filter,
        ?string $search,
    ): LengthAwarePaginator {
        $items = $rows->map(fn (object $row) => CustomerTimelineEvent::fromRow($row));

        return new PaginatorInstance(
            $items,
            $total,
            self::PER_PAGE,
            $page,
            ['path' => $this->paginationPath($customer, $filter, $search), 'pageName' => 'timeline_page'],
        );
    }

    protected function emptyPaginator(int $page, Customer $customer, string $filter, ?string $search): LengthAwarePaginator
    {
        return new PaginatorInstance(
            collect(),
            0,
            self::PER_PAGE,
            $page,
            ['path' => $this->paginationPath($customer, $filter, $search), 'pageName' => 'timeline_page'],
        );
    }

    protected function paginationPath(Customer $customer, string $filter, ?string $search): string
    {
        return route('admin.crm.customers.show', array_filter([
            'customer' => $customer,
            'tab' => Customer360WorkspaceService::TAB_TIMELINE,
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

        if ($driver === 'sqlite') {
            return implode(' || ', $parts);
        }

        return 'CONCAT('.implode(', ', $parts).')';
    }

    /**
     * @param  array<string, string>  $pairs column alias => SQL expression
     */
    protected function jsonObject(array $pairs): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $chunks = collect($pairs)->map(fn ($expr, $key) => "'{$key}', {$expr}")->implode(', ');

            return "json_object({$chunks})";
        }

        $chunks = collect($pairs)->map(fn ($expr, $key) => "'{$key}', {$expr}")->implode(', ');

        return "JSON_OBJECT({$chunks})";
    }

    protected function sqlSubstr(string $column, int $length): string
    {
        return "SUBSTR({$column}, 1, {$length})";
    }

    /**
     * Guard: timeline must paginate via union, never load all rows into memory.
     */
    public function usesDatabaseUnion(): bool
    {
        return true;
    }
}
