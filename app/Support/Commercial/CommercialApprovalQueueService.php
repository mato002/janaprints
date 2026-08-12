<?php

namespace App\Support\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommercialApprovalQueueService
{
    public const TAB_PENDING = 'pending';

    public const TAB_APPROVED = 'approved';

    public const TAB_REJECTED = 'rejected';

    public const TAB_ALL = 'all';

    public const PER_PAGE = 25;

    /**
     * @param  array{
     *     tab?: string,
     *     type?: string,
     *     q?: string,
     *     branch_id?: int|null,
     *     requested_by?: int|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     page?: int
     * }  $filters
     * @return array{
     *     tab: string,
     *     filters: array<string, mixed>,
     *     counts: array{pending: int, approved: int, rejected: int, all: int},
     *     pending_summary: array{quotation: int, sales_order: int, artwork: int, total: int},
     *     items: LengthAwarePaginator,
     *     branches: Collection<int, Branch>,
     *     requesters: Collection<int, User>,
     * }
     */
    public function workspace(int $companyId, ?int $tenantBranchId, array $filters = []): array
    {
        $tab = $this->normalizeTab($filters['tab'] ?? null);
        $normalized = [
            'tab' => $tab,
            'type' => $this->normalizeType($filters['type'] ?? null),
            'q' => trim((string) ($filters['q'] ?? '')),
            'branch_id' => filled($filters['branch_id'] ?? null) ? (int) $filters['branch_id'] : null,
            'requested_by' => filled($filters['requested_by'] ?? null) ? (int) $filters['requested_by'] : null,
            'date_from' => filled($filters['date_from'] ?? null) ? (string) $filters['date_from'] : null,
            'date_to' => filled($filters['date_to'] ?? null) ? (string) $filters['date_to'] : null,
        ];

        $scopeBranchId = $normalized['branch_id'] ?? $tenantBranchId;

        return [
            'tab' => $tab,
            'filters' => $normalized,
            'counts' => $this->counts($companyId, $tenantBranchId),
            'pending_summary' => $this->pendingSummary($companyId, $tenantBranchId),
            'items' => $this->paginateItems($companyId, $scopeBranchId, $normalized),
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'requesters' => $this->requesterOptions($companyId),
        ];
    }

    /**
     * Legacy section payload for any callers still expecting five buckets.
     *
     * @return array{
     *     pending_quotations: Collection<int, array<string, mixed>>,
     *     pending_sales_orders: Collection<int, array<string, mixed>>,
     *     pending_artwork: Collection<int, array<string, mixed>>,
     *     recently_approved: Collection<int, array<string, mixed>>,
     *     recently_rejected: Collection<int, array<string, mixed>>,
     * }
     */
    public function present(int $companyId, ?int $branchId = null): array
    {
        $pending = $this->workspace($companyId, $branchId, ['tab' => self::TAB_PENDING, 'type' => 'all']);
        $approved = $this->workspace($companyId, $branchId, ['tab' => self::TAB_APPROVED, 'type' => 'all']);
        $rejected = $this->workspace($companyId, $branchId, ['tab' => self::TAB_REJECTED, 'type' => 'all']);

        $pendingItems = collect($pending['items']->items());

        return [
            'pending_quotations' => $pendingItems->where('type', 'quotation')->values(),
            'pending_sales_orders' => $pendingItems->where('type', 'sales_order')->values(),
            'pending_artwork' => $pendingItems->where('type', 'artwork')->values(),
            'recently_approved' => collect($approved['items']->items()),
            'recently_rejected' => collect($rejected['items']->items()),
        ];
    }

    /**
     * @return array{pending: int, approved: int, rejected: int, all: int}
     */
    protected function counts(int $companyId, ?int $branchId): array
    {
        $pending = $this->pendingSummary($companyId, $branchId)['total'];
        $approved = $this->countForTab($companyId, $branchId, self::TAB_APPROVED);
        $rejected = $this->countForTab($companyId, $branchId, self::TAB_REJECTED);

        return [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'all' => $pending + $approved + $rejected,
        ];
    }

    /**
     * @return array{quotation: int, sales_order: int, artwork: int, total: int}
     */
    protected function pendingSummary(int $companyId, ?int $branchId): array
    {
        $quotation = $this->quotationQuery($companyId, $branchId)
            ->where('status', QuotationStatus::PendingApproval)
            ->count();
        $salesOrder = $this->salesOrderQuery($companyId, $branchId)
            ->where('status', SalesOrderStatus::Draft)
            ->count();
        $artwork = $this->artworkQuery($companyId, $branchId)
            ->where('status', ArtworkRequestStatus::Submitted)
            ->count();

        return [
            'quotation' => $quotation,
            'sales_order' => $salesOrder,
            'artwork' => $artwork,
            'total' => $quotation + $salesOrder + $artwork,
        ];
    }

    protected function countForTab(int $companyId, ?int $branchId, string $tab): int
    {
        $filters = ['tab' => $tab, 'type' => 'all', 'q' => '', 'branch_id' => null, 'requested_by' => null, 'date_from' => null, 'date_to' => null];

        return $this->unionQuery($companyId, $branchId, $filters)->count();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function paginateItems(int $companyId, ?int $branchId, array $filters): LengthAwarePaginator
    {
        $page = max(1, (int) request()->integer('page', 1));
        $perPage = self::PER_PAGE;

        $union = $this->unionQuery($companyId, $branchId, $filters);
        $total = (clone $union)->count();

        $sortDirection = $filters['tab'] === self::TAB_PENDING ? 'asc' : 'desc';

        $keys = (clone $union)
            ->orderBy('sort_at', $sortDirection)
            ->orderBy('document')
            ->forPage($page, $perPage)
            ->get();

        $rows = $this->hydrateRows($keys);

        return new Paginator(
            $rows,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function unionQuery(int $companyId, ?int $branchId, array $filters): QueryBuilder
    {
        $parts = [];

        if (in_array($filters['type'], ['all', 'quotation'], true)) {
            $parts[] = $this->quotationUnionSelect($companyId, $branchId, $filters);
        }

        if (in_array($filters['type'], ['all', 'sales_order'], true) && $filters['tab'] === self::TAB_PENDING) {
            $parts[] = $this->salesOrderUnionSelect($companyId, $branchId, $filters);
        }

        if (in_array($filters['type'], ['all', 'artwork'], true)) {
            $parts[] = $this->artworkUnionSelect($companyId, $branchId, $filters);
        }

        if ($parts === []) {
            return DB::query()
                ->fromSub(
                    DB::query()->selectRaw("'quotation' as type, 0 as id, '' as document, null as sort_at")->whereRaw('1 = 0'),
                    'approval_queue',
                );
        }

        $union = array_shift($parts);
        foreach ($parts as $part) {
            $union->unionAll($part);
        }

        return DB::query()->fromSub($union, 'approval_queue');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function quotationUnionSelect(int $companyId, ?int $branchId, array $filters): Builder
    {
        $query = $this->quotationQuery($companyId, $branchId)
            ->selectRaw("
                'quotation' as type,
                quotations.id as id,
                quotations.quotation_number as document,
                CASE
                    WHEN quotations.status = ? THEN quotations.updated_at
                    ELSE COALESCE(quotations.approved_at, quotations.updated_at)
                END as sort_at
            ", [QuotationStatus::PendingApproval->value]);

        $this->applyQuotationTabFilter($query, $filters['tab']);
        $this->applySharedFilters($query, $filters, [
            'document' => 'quotations.quotation_number',
            'customer_id' => 'quotations.customer_id',
            'requested_by' => 'quotations.prepared_by',
            'date_column' => 'COALESCE(quotations.approved_at, quotations.updated_at)',
            'pending_date_column' => 'quotations.updated_at',
        ]);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function salesOrderUnionSelect(int $companyId, ?int $branchId, array $filters): Builder
    {
        $query = $this->salesOrderQuery($companyId, $branchId)
            ->selectRaw("
                'sales_order' as type,
                sales_orders.id as id,
                sales_orders.order_number as document,
                sales_orders.updated_at as sort_at
            ");

        $this->applySalesOrderTabFilter($query, $filters['tab']);
        $this->applySharedFilters($query, $filters, [
            'document' => 'sales_orders.order_number',
            'customer_id' => 'sales_orders.customer_id',
            'requested_by' => 'sales_orders.created_by',
            'date_column' => 'sales_orders.updated_at',
            'pending_date_column' => 'sales_orders.updated_at',
        ]);

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function artworkUnionSelect(int $companyId, ?int $branchId, array $filters): Builder
    {
        $query = $this->artworkQuery($companyId, $branchId)
            ->selectRaw("
                'artwork' as type,
                artwork_requests.id as id,
                artwork_requests.request_number as document,
                artwork_requests.updated_at as sort_at
            ");

        $this->applyArtworkTabFilter($query, $filters['tab']);
        $this->applySharedFilters($query, $filters, [
            'document' => 'artwork_requests.request_number',
            'customer_id' => 'artwork_requests.customer_id',
            'requested_by' => 'artwork_requests.assigned_designer_id',
            'date_column' => 'artwork_requests.updated_at',
            'pending_date_column' => 'artwork_requests.updated_at',
        ]);

        return $query;
    }

    protected function applyQuotationTabFilter(Builder $query, string $tab): void
    {
        match ($tab) {
            self::TAB_PENDING => $query->where('quotations.status', QuotationStatus::PendingApproval),
            self::TAB_APPROVED => $query->where(function (Builder $inner) {
                $inner->whereNotNull('quotations.approved_at')
                    ->orWhereIn('quotations.status', [
                        QuotationStatus::Approved,
                        QuotationStatus::Sent,
                        QuotationStatus::Viewed,
                        QuotationStatus::Accepted,
                        QuotationStatus::Converted,
                    ]);
            }),
            self::TAB_REJECTED => $query->where('quotations.status', QuotationStatus::Rejected),
            default => $query->where(function (Builder $inner) {
                $inner->where('quotations.status', QuotationStatus::PendingApproval)
                    ->orWhereNotNull('quotations.approved_at')
                    ->orWhereIn('quotations.status', [
                        QuotationStatus::Approved,
                        QuotationStatus::Sent,
                        QuotationStatus::Viewed,
                        QuotationStatus::Accepted,
                        QuotationStatus::Converted,
                        QuotationStatus::Rejected,
                    ]);
            }),
        };
    }

    protected function applySalesOrderTabFilter(Builder $query, string $tab): void
    {
        if ($tab === self::TAB_PENDING) {
            $query->where('sales_orders.status', SalesOrderStatus::Draft);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    protected function applyArtworkTabFilter(Builder $query, string $tab): void
    {
        match ($tab) {
            self::TAB_PENDING => $query->where('artwork_requests.status', ArtworkRequestStatus::Submitted),
            self::TAB_APPROVED => $query->where('artwork_requests.status', ArtworkRequestStatus::Approved),
            self::TAB_REJECTED => $query->where('artwork_requests.status', ArtworkRequestStatus::Rejected),
            default => $query->whereIn('artwork_requests.status', [
                ArtworkRequestStatus::Submitted,
                ArtworkRequestStatus::Approved,
                ArtworkRequestStatus::Rejected,
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{document: string, customer_id: string, requested_by: string, date_column: string, pending_date_column: string}  $columns
     */
    protected function applySharedFilters(Builder $query, array $filters, array $columns): void
    {
        if ($filters['q'] !== '') {
            $term = '%'.$filters['q'].'%';
            $query->where(function (Builder $inner) use ($term, $columns) {
                $inner->where($columns['document'], 'like', $term)
                    ->orWhereHas('customer', function (Builder $customer) use ($term) {
                        $customer->where('company_name', 'like', $term);
                    });
            });
        }

        if ($filters['requested_by']) {
            $query->where($columns['requested_by'], $filters['requested_by']);
        }

        $dateColumn = $filters['tab'] === self::TAB_PENDING
            ? $columns['pending_date_column']
            : $columns['date_column'];

        if ($filters['date_from']) {
            $query->whereRaw("DATE({$dateColumn}) >= ?", [$filters['date_from']]);
        }

        if ($filters['date_to']) {
            $query->whereRaw("DATE({$dateColumn}) <= ?", [$filters['date_to']]);
        }
    }

    /**
     * @param  Collection<int, object>  $keys
     * @return Collection<int, array<string, mixed>>
     */
    protected function hydrateRows(Collection $keys): Collection
    {
        if ($keys->isEmpty()) {
            return collect();
        }

        $quotationIds = $keys->where('type', 'quotation')->pluck('id')->all();
        $orderIds = $keys->where('type', 'sales_order')->pluck('id')->all();
        $artworkIds = $keys->where('type', 'artwork')->pluck('id')->all();

        $quotations = $quotationIds === []
            ? collect()
            : Quotation::query()
                ->with(['customer:id,company_name', 'branch:id,name', 'preparer:id,name', 'approver:id,name'])
                ->whereIn('id', $quotationIds)
                ->get()
                ->keyBy('id');

        $orders = $orderIds === []
            ? collect()
            : SalesOrder::query()
                ->with(['customer:id,company_name', 'branch:id,name', 'creator:id,name'])
                ->whereIn('id', $orderIds)
                ->get()
                ->keyBy('id');

        $artwork = $artworkIds === []
            ? collect()
            : ArtworkRequest::query()
                ->with(['customer:id,company_name', 'branch:id,name', 'assignedDesigner:id,name'])
                ->whereIn('id', $artworkIds)
                ->get()
                ->keyBy('id');

        return $keys->map(function (object $key) use ($quotations, $orders, $artwork) {
            return match ($key->type) {
                'quotation' => ($model = $quotations->get($key->id))
                    ? $this->mapQuotationRow($model, $this->bucketForQuotation($model))
                    : null,
                'sales_order' => ($model = $orders->get($key->id))
                    ? $this->mapSalesOrderRow($model, $this->bucketForSalesOrder($model))
                    : null,
                'artwork' => ($model = $artwork->get($key->id))
                    ? $this->mapArtworkRow($model, $this->bucketForArtwork($model))
                    : null,
                default => null,
            };
        })->filter()->values();
    }

    protected function bucketForQuotation(Quotation $quotation): string
    {
        return match (true) {
            $quotation->status === QuotationStatus::PendingApproval => self::TAB_PENDING,
            $quotation->status === QuotationStatus::Rejected => self::TAB_REJECTED,
            default => self::TAB_APPROVED,
        };
    }

    protected function bucketForSalesOrder(SalesOrder $order): string
    {
        return $order->status === SalesOrderStatus::Draft
            ? self::TAB_PENDING
            : self::TAB_APPROVED;
    }

    protected function bucketForArtwork(ArtworkRequest $request): string
    {
        return match ($request->status) {
            ArtworkRequestStatus::Submitted => self::TAB_PENDING,
            ArtworkRequestStatus::Rejected => self::TAB_REJECTED,
            default => self::TAB_APPROVED,
        };
    }

    /**
     * @return Collection<int, User>
     */
    protected function requesterOptions(int $companyId): Collection
    {
        return User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name']);
    }

    protected function normalizeTab(?string $tab): string
    {
        return in_array($tab, [self::TAB_PENDING, self::TAB_APPROVED, self::TAB_REJECTED, self::TAB_ALL], true)
            ? $tab
            : self::TAB_PENDING;
    }

    protected function normalizeType(?string $type): string
    {
        return in_array($type, ['all', 'quotation', 'sales_order', 'artwork'], true)
            ? $type
            : 'all';
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapQuotationRow(Quotation $quotation, string $bucket): array
    {
        $submittedAt = $quotation->status === QuotationStatus::PendingApproval
            ? $quotation->updated_at
            : ($quotation->approved_at ?? $quotation->updated_at);

        return [
            'type' => 'quotation',
            'type_label' => __('Quotation'),
            'document' => $quotation->quotation_number,
            'customer' => $quotation->customer?->company_name ?? __('Walk-in'),
            'branch' => $quotation->branch?->name ?? '—',
            'amount' => number_format((float) $quotation->total_amount, 2),
            'requested_by' => $quotation->preparer?->name ?? $quotation->approver?->name ?? '—',
            'submitted_at' => $submittedAt,
            'age_days' => $this->ageDays($submittedAt),
            'age_label' => $this->ageLabel($submittedAt),
            'status' => $quotation->status->value,
            'status_label' => str($quotation->status->value)->headline(),
            'view_url' => route('admin.quotations.show', $quotation),
            'approve_url' => $bucket === self::TAB_PENDING ? route('admin.quotations.approve', $quotation) : null,
            'reject_url' => $bucket === self::TAB_PENDING ? route('admin.quotations.reject', $quotation) : null,
            'bucket' => $bucket,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapSalesOrderRow(SalesOrder $order, string $bucket): array
    {
        return [
            'type' => 'sales_order',
            'type_label' => __('Sales order'),
            'document' => $order->order_number,
            'customer' => $order->customer?->company_name ?? '—',
            'branch' => $order->branch?->name ?? '—',
            'amount' => number_format((float) $order->total_amount, 2),
            'requested_by' => $order->creator?->name ?? '—',
            'submitted_at' => $order->updated_at,
            'age_days' => $this->ageDays($order->updated_at),
            'age_label' => $this->ageLabel($order->updated_at),
            'status' => $order->status->value,
            'status_label' => str($order->status->value)->headline(),
            'view_url' => route('admin.sales-orders.show', $order),
            'approve_url' => $bucket === self::TAB_PENDING ? route('admin.sales-orders.confirm', $order) : null,
            'reject_url' => null,
            'bucket' => $bucket,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapArtworkRow(ArtworkRequest $request, string $bucket): array
    {
        return [
            'type' => 'artwork',
            'type_label' => __('Artwork'),
            'document' => $request->request_number,
            'customer' => $request->customer?->company_name ?? '—',
            'branch' => $request->branch?->name ?? '—',
            'amount' => '—',
            'requested_by' => $request->assignedDesigner?->name ?? '—',
            'submitted_at' => $request->updated_at,
            'age_days' => $this->ageDays($request->updated_at),
            'age_label' => $this->ageLabel($request->updated_at),
            'status' => $request->status->value,
            'status_label' => str($request->status->value)->headline(),
            'view_url' => route('admin.artwork.show', $request),
            'approve_url' => $bucket === self::TAB_PENDING ? route('admin.artwork.approve', $request) : null,
            'reject_url' => null,
            'bucket' => $bucket,
        ];
    }

    protected function ageDays(mixed $submittedAt): int
    {
        if (! $submittedAt) {
            return 0;
        }

        return (int) $submittedAt->diffInDays(now());
    }

    protected function ageLabel(mixed $submittedAt): string
    {
        $days = $this->ageDays($submittedAt);

        return $days === 0 ? __('Today') : __(':count days', ['count' => $days]);
    }

    protected function quotationQuery(int $companyId, ?int $branchId): Builder
    {
        return Quotation::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    protected function salesOrderQuery(int $companyId, ?int $branchId): Builder
    {
        return SalesOrder::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    protected function artworkQuery(int $companyId, ?int $branchId): Builder
    {
        return ArtworkRequest::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }
}
