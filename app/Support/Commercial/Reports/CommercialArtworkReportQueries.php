<?php

namespace App\Support\Commercial\Reports;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommercialArtworkReportQueries
{
    public const PER_PAGE = 25;

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * @return list<string>
     */
    public function pendingStatuses(): array
    {
        return [
            ArtworkRequestStatus::Requested->value,
            ArtworkRequestStatus::InDesign->value,
            ArtworkRequestStatus::Submitted->value,
            ArtworkRequestStatus::RevisionRequested->value,
        ];
    }

    /**
     * @return list<string>
     */
    public function terminalStatuses(): array
    {
        return [
            ArtworkRequestStatus::Approved->value,
            ArtworkRequestStatus::Rejected->value,
        ];
    }

    public function baseRequestQuery(CommercialArtworkReportScope $scope): Builder
    {
        $query = ArtworkRequest::query()
            ->where('artwork_requests.company_id', $scope->companyId)
            ->whereDate('artwork_requests.created_at', '>=', $scope->fromDate)
            ->whereDate('artwork_requests.created_at', '<=', $scope->toDate);

        if ($scope->branchId !== null) {
            $query->where('artwork_requests.branch_id', $scope->branchId);
        }

        if ($scope->customerId !== null) {
            $query->where('artwork_requests.customer_id', $scope->customerId);
        }

        if ($scope->designerId !== null) {
            $query->where('artwork_requests.assigned_designer_id', $scope->designerId);
        }

        if ($scope->status !== null && $scope->status !== '') {
            $query->where('artwork_requests.status', $scope->status);
        }

        if ($scope->approvalStatus !== null && $this->hasTable('artwork_approvals')) {
            $query->whereExists(function ($sub) use ($scope) {
                $sub->select(DB::raw(1))
                    ->from('artwork_approvals')
                    ->whereColumn('artwork_approvals.artwork_request_id', 'artwork_requests.id')
                    ->where('artwork_approvals.company_id', $scope->companyId)
                    ->where('artwork_approvals.decision', $scope->approvalStatus);
            });
        }

        if ($scope->delayStatus === 'delayed') {
            $query->whereNotNull('artwork_requests.due_date')
                ->whereDate('artwork_requests.due_date', '<', now()->toDateString())
                ->whereNotIn('artwork_requests.status', $this->terminalStatuses());
        } elseif ($scope->delayStatus === 'on_time') {
            $query->where(function (Builder $inner) {
                $inner->whereNull('artwork_requests.due_date')
                    ->orWhereDate('artwork_requests.due_date', '>=', now()->toDateString())
                    ->orWhereIn('artwork_requests.status', $this->terminalStatuses());
            });
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('artwork_requests.request_number', 'like', $term)
                    ->orWhere('artwork_requests.title', 'like', $term);
            });
        }

        return $query;
    }

    public function totalRequests(CommercialArtworkReportScope $scope): int
    {
        if (! $this->hasTable('artwork_requests')) {
            return 0;
        }

        return (int) $this->baseRequestQuery($scope)->count();
    }

    public function pendingRequests(CommercialArtworkReportScope $scope): int
    {
        if (! $this->hasTable('artwork_requests')) {
            return 0;
        }

        return (int) $this->baseRequestQuery($scope)
            ->whereIn('artwork_requests.status', $this->pendingStatuses())
            ->count();
    }

    public function approvedRequests(CommercialArtworkReportScope $scope): int
    {
        if (! $this->hasTable('artwork_requests')) {
            return 0;
        }

        return (int) $this->baseRequestQuery($scope)
            ->where('artwork_requests.status', ArtworkRequestStatus::Approved->value)
            ->count();
    }

    public function rejectedRequests(CommercialArtworkReportScope $scope): int
    {
        if (! $this->hasTable('artwork_requests')) {
            return 0;
        }

        return (int) $this->baseRequestQuery($scope)
            ->where('artwork_requests.status', ArtworkRequestStatus::Rejected->value)
            ->count();
    }

    public function delayedRequests(CommercialArtworkReportScope $scope): int
    {
        if (! $this->hasTable('artwork_requests')) {
            return 0;
        }

        return (int) $this->baseRequestQuery($scope)
            ->whereNotNull('artwork_requests.due_date')
            ->whereDate('artwork_requests.due_date', '<', now()->toDateString())
            ->whereNotIn('artwork_requests.status', $this->terminalStatuses())
            ->count();
    }

    public function averageApprovalTimeHours(CommercialArtworkReportScope $scope): ?float
    {
        if (! $this->hasTable('artwork_approvals')) {
            return null;
        }

        $query = DB::table('artwork_requests as ar')
            ->join('artwork_approvals as aa', function ($join) {
                $join->on('aa.artwork_request_id', '=', 'ar.id')
                    ->where('aa.decision', ArtworkApprovalDecision::Approved->value);
            })
            ->where('ar.company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('ar.branch_id', $scope->branchId))
            ->whereDate('ar.created_at', '>=', $scope->fromDate)
            ->whereDate('ar.created_at', '<=', $scope->toDate)
            ->when($scope->customerId, fn ($q) => $q->where('ar.customer_id', $scope->customerId))
            ->when($scope->designerId, fn ($q) => $q->where('ar.assigned_designer_id', $scope->designerId));

        $avg = $this->isSqlite()
            ? $query->selectRaw('AVG((julianday(aa.created_at) - julianday(ar.created_at)) * 24) as avg_hours')->value('avg_hours')
            : $query->selectRaw('AVG(TIMESTAMPDIFF(HOUR, ar.created_at, aa.created_at)) as avg_hours')->value('avg_hours');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function averageRevisionCount(CommercialArtworkReportScope $scope): ?float
    {
        if (! $this->hasTable('artwork_requests')) {
            return null;
        }

        if ($this->hasTable('artwork_versions')) {
            $avg = DB::table('artwork_requests as ar')
                ->leftJoin('artwork_versions as av', 'av.artwork_request_id', '=', 'ar.id')
                ->where('ar.company_id', $scope->companyId)
                ->when($scope->branchId, fn ($q) => $q->where('ar.branch_id', $scope->branchId))
                ->whereDate('ar.created_at', '>=', $scope->fromDate)
                ->whereDate('ar.created_at', '<=', $scope->toDate)
                ->groupBy('ar.id')
                ->selectRaw('COUNT(av.id) as version_count')
                ->get()
                ->avg('version_count');

            return $avg !== null ? round((float) $avg, 1) : null;
        }

        $avg = $this->baseRequestQuery($scope)->avg('current_version');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function designerThroughput(CommercialArtworkReportScope $scope): int
    {
        if (! $this->hasTable('artwork_requests')) {
            return 0;
        }

        return (int) $this->baseRequestQuery($scope)
            ->where('artwork_requests.status', ArtworkRequestStatus::Approved->value)
            ->whereNotNull('artwork_requests.assigned_designer_id')
            ->count();
    }

    public function approvalRatePercent(CommercialArtworkReportScope $scope): ?float
    {
        $approved = $this->approvedRequests($scope);
        $rejected = $this->rejectedRequests($scope);
        $decided = $approved + $rejected;

        if ($decided <= 0) {
            return null;
        }

        return round(($approved / $decided) * 100, 1);
    }

    public function formatHours(?float $hours): string
    {
        if ($hours === null) {
            return '—';
        }

        if ($hours < 24) {
            return $hours.' '.__('hrs');
        }

        return round($hours / 24, 1).' '.__('days');
    }

    /**
     * @param  list<string>|null  $statuses
     */
    public function paginateRequestList(CommercialArtworkReportScope $scope, ?array $statuses = null, bool $withDelay = false): LengthAwarePaginator
    {
        if (! $this->hasTable('artwork_requests')) {
            return $this->emptyPaginator($scope);
        }

        $query = $this->baseRequestQuery($scope);

        if ($statuses !== null) {
            $query->whereIn('artwork_requests.status', $statuses);
        }

        $paginator = $query
            ->select([
                'artwork_requests.id',
                'artwork_requests.request_number',
                'artwork_requests.title',
                'artwork_requests.customer_id',
                'artwork_requests.branch_id',
                'artwork_requests.assigned_designer_id',
                'artwork_requests.priority',
                'artwork_requests.status',
                'artwork_requests.due_date',
                'artwork_requests.current_version',
                'artwork_requests.created_at',
            ])
            ->orderByDesc('artwork_requests.created_at')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $customerMap = $this->customerMap($paginator->getCollection()->pluck('customer_id'));
        $branchMap = $this->branchMap($paginator->getCollection()->pluck('branch_id'));
        $designerMap = $this->userMap($paginator->getCollection()->pluck('assigned_designer_id'));

        return $paginator->through(function ($row) use ($customerMap, $branchMap, $designerMap, $withDelay) {
            $isDelayed = $row->due_date
                && Carbon::parse($row->due_date)->lt(now()->startOfDay())
                && ! in_array($row->status->value, $this->terminalStatuses(), true);

            $base = [
                'request' => $row->request_number,
                'title' => $row->title,
                'customer' => $customerMap[$row->customer_id] ?? '—',
                'branch' => $branchMap[$row->branch_id] ?? '—',
                'designer' => $designerMap[$row->assigned_designer_id] ?? __('Unassigned'),
                'priority' => ucfirst($row->priority->value),
                'status' => ucfirst(str_replace('_', ' ', $row->status->value)),
                'due_date' => $row->due_date?->format('d M Y') ?? '—',
            ];

            if ($withDelay) {
                return array_merge($base, [
                    'delay' => $isDelayed ? __('Delayed') : __('On time'),
                    'created' => $row->created_at?->format('d M Y') ?? '—',
                ]);
            }

            return array_merge($base, [
                'versions' => (string) $row->current_version,
                'created' => $row->created_at?->format('d M Y') ?? '—',
            ]);
        });
    }

    public function paginateTurnaround(CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('artwork_requests') || ! $this->hasTable('artwork_approvals')) {
            return $this->emptyPaginator($scope);
        }

        $approvalSub = DB::table('artwork_approvals')
            ->select('artwork_request_id', DB::raw('MIN(created_at) as approved_at'))
            ->where('decision', ArtworkApprovalDecision::Approved->value)
            ->groupBy('artwork_request_id');

        $turnaroundExpr = $this->isSqlite()
            ? '(julianday(first_approval.approved_at) - julianday(artwork_requests.created_at)) * 24'
            : 'TIMESTAMPDIFF(HOUR, artwork_requests.created_at, first_approval.approved_at)';

        $query = $this->baseRequestQuery($scope)
            ->joinSub($approvalSub, 'first_approval', 'first_approval.artwork_request_id', '=', 'artwork_requests.id')
            ->select([
                'artwork_requests.request_number',
                'artwork_requests.title',
                'artwork_requests.customer_id',
                'artwork_requests.created_at',
                'first_approval.approved_at',
                DB::raw("{$turnaroundExpr} as turnaround_hours"),
            ])
            ->orderByDesc('turnaround_hours');

        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);
        $customerMap = $this->customerMap($paginator->getCollection()->pluck('customer_id'));

        return $paginator->through(fn ($row) => [
            'request' => $row->request_number,
            'title' => $row->title,
            'customer' => $customerMap[$row->customer_id] ?? '—',
            'created' => Carbon::parse($row->created_at)->format('d M Y'),
            'approved' => Carbon::parse($row->approved_at)->format('d M Y'),
            'turnaround' => $this->formatHours((float) $row->turnaround_hours),
        ]);
    }

    public function paginateDesignerPerformance(CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('artwork_requests')) {
            return $this->emptyPaginator($scope);
        }

        $rows = DB::table('artwork_requests')
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->when($scope->customerId, fn ($q) => $q->where('customer_id', $scope->customerId))
            ->when($scope->designerId, fn ($q) => $q->where('assigned_designer_id', $scope->designerId))
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->whereNotNull('assigned_designer_id')
            ->select(
                'assigned_designer_id',
                DB::raw('COUNT(*) as total_assigned'),
                DB::raw("SUM(CASE WHEN status = '".ArtworkRequestStatus::Approved->value."' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status IN ('".implode("','", $this->pendingStatuses())."') THEN 1 ELSE 0 END) as pending"),
            )
            ->groupBy('assigned_designer_id')
            ->orderByDesc('completed')
            ->get();

        $designerMap = $this->userMap($rows->pluck('assigned_designer_id'));
        $mapped = $rows->map(fn ($row) => [
            'designer' => $designerMap[$row->assigned_designer_id] ?? __('Unknown'),
            'assigned' => (string) $row->total_assigned,
            'completed' => (string) $row->completed,
            'pending' => (string) $row->pending,
            'throughput' => (string) $row->completed,
        ]);

        return $this->paginateCollection($mapped, $scope);
    }

    public function paginateRevisionAnalysis(CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('artwork_requests')) {
            return $this->emptyPaginator($scope);
        }

        $query = $this->baseRequestQuery($scope);

        if ($this->hasTable('artwork_versions')) {
            $versionSub = DB::table('artwork_versions')
                ->select('artwork_request_id', DB::raw('COUNT(*) as version_count'))
                ->groupBy('artwork_request_id');

            $query->leftJoinSub($versionSub, 'version_stats', 'version_stats.artwork_request_id', '=', 'artwork_requests.id')
                ->select([
                    'artwork_requests.request_number',
                    'artwork_requests.title',
                    'artwork_requests.customer_id',
                    'artwork_requests.status',
                    DB::raw('COALESCE(version_stats.version_count, 0) as version_count'),
                ])
                ->orderByDesc('version_count');
        } else {
            $query->select([
                'artwork_requests.request_number',
                'artwork_requests.title',
                'artwork_requests.customer_id',
                'artwork_requests.status',
                'artwork_requests.current_version as version_count',
            ])
                ->orderByDesc('artwork_requests.current_version');
        }

        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);
        $customerMap = $this->customerMap($paginator->getCollection()->pluck('customer_id'));

        return $paginator->through(function ($row) use ($customerMap) {
            $status = $row->status instanceof ArtworkRequestStatus
                ? $row->status->value
                : (string) $row->status;

            return [
                'request' => $row->request_number,
                'title' => $row->title,
                'customer' => $customerMap[$row->customer_id] ?? '—',
                'versions' => (string) $row->version_count,
                'revisions' => (string) max(0, (int) $row->version_count - 1),
                'status' => ucfirst(str_replace('_', ' ', $status)),
            ];
        });
    }

    public function paginateByCustomer(CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('artwork_requests')) {
            return $this->emptyPaginator($scope);
        }

        $rows = DB::table('artwork_requests')
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->when($scope->customerId, fn ($q) => $q->where('customer_id', $scope->customerId))
            ->when($scope->designerId, fn ($q) => $q->where('assigned_designer_id', $scope->designerId))
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status IN ('".implode("','", $this->pendingStatuses())."') THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = '".ArtworkRequestStatus::Approved->value."' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = '".ArtworkRequestStatus::Rejected->value."' THEN 1 ELSE 0 END) as rejected"),
            )
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->get();

        $customerMap = $this->customerMap($rows->pluck('customer_id'));
        $mapped = $rows->map(fn ($row) => [
            'customer' => $customerMap[$row->customer_id] ?? __('Unknown'),
            'requests' => (string) $row->total,
            'pending' => (string) $row->pending,
            'approved' => (string) $row->approved,
            'rejected' => (string) $row->rejected,
        ]);

        return $this->paginateCollection($mapped, $scope);
    }

    public function paginateByBranch(CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('artwork_requests')) {
            return $this->emptyPaginator($scope);
        }

        $rows = DB::table('artwork_requests')
            ->where('company_id', $scope->companyId)
            ->when($scope->branchId, fn ($q) => $q->where('branch_id', $scope->branchId))
            ->when($scope->customerId, fn ($q) => $q->where('customer_id', $scope->customerId))
            ->when($scope->designerId, fn ($q) => $q->where('assigned_designer_id', $scope->designerId))
            ->whereDate('created_at', '>=', $scope->fromDate)
            ->whereDate('created_at', '<=', $scope->toDate)
            ->select(
                'branch_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status IN ('".implode("','", $this->pendingStatuses())."') THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status = '".ArtworkRequestStatus::Approved->value."' THEN 1 ELSE 0 END) as approved"),
                DB::raw("SUM(CASE WHEN status = '".ArtworkRequestStatus::Rejected->value."' THEN 1 ELSE 0 END) as rejected"),
            )
            ->groupBy('branch_id')
            ->orderByDesc('total')
            ->get();

        $branchMap = $this->branchMap($rows->pluck('branch_id'));
        $mapped = $rows->map(fn ($row) => [
            'branch' => $branchMap[$row->branch_id] ?? __('Unknown'),
            'requests' => (string) $row->total,
            'pending' => (string) $row->pending,
            'approved' => (string) $row->approved,
            'rejected' => (string) $row->rejected,
        ]);

        return $this->paginateCollection($mapped, $scope);
    }

    public function paginateDelays(CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        $delayedScope = new CommercialArtworkReportScope(
            companyId: $scope->companyId,
            branchId: $scope->branchId,
            fromDate: $scope->fromDate,
            toDate: $scope->toDate,
            customerId: $scope->customerId,
            designerId: $scope->designerId,
            status: $scope->status,
            approvalStatus: $scope->approvalStatus,
            delayStatus: 'delayed',
            search: $scope->search,
            tab: $scope->tab,
            page: $scope->page,
        );

        return $this->paginateRequestList($delayedScope, null, true);
    }

    /**
     * @param  Collection<int, mixed>  $ids
     * @return array<int, string>
     */
    protected function customerMap(Collection $ids): array
    {
        if (! $this->hasTable('customers') || $ids->filter()->isEmpty()) {
            return [];
        }

        return Customer::query()
            ->whereIn('id', $ids->filter()->unique())
            ->pluck('company_name', 'id')
            ->all();
    }

    /**
     * @param  Collection<int, mixed>  $ids
     * @return array<int, string>
     */
    protected function branchMap(Collection $ids): array
    {
        if (! $this->hasTable('branches') || $ids->filter()->isEmpty()) {
            return [];
        }

        return Branch::query()
            ->whereIn('id', $ids->filter()->unique())
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @param  Collection<int, mixed>  $ids
     * @return array<int, string>
     */
    protected function userMap(Collection $ids): array
    {
        if (! $this->hasTable('users') || $ids->filter()->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('id', $ids->filter()->unique())
            ->pluck('name', 'id')
            ->all();
    }

    protected function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }

    protected function emptyPaginator(CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        return new Paginator([], 0, self::PER_PAGE, $scope->page);
    }

    /**
     * @param  Collection<int, array<string, string>>  $rows
     */
    protected function paginateCollection(Collection $rows, CommercialArtworkReportScope $scope): LengthAwarePaginator
    {
        $page = $scope->page;
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        return new Paginator($items, $total, self::PER_PAGE, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }
}
