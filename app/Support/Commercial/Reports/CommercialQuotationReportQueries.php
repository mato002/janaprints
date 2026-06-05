<?php

namespace App\Support\Commercial\Reports;

use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommercialQuotationReportQueries
{
    public const PER_PAGE = 25;

    /**
     * @return list<string>
     */
    public function issuedStatuses(): array
    {
        return [
            QuotationStatus::Sent->value,
            QuotationStatus::Viewed->value,
            QuotationStatus::Accepted->value,
            QuotationStatus::Rejected->value,
            QuotationStatus::Expired->value,
            QuotationStatus::Converted->value,
        ];
    }

    /**
     * @return list<string>
     */
    public function openStatuses(): array
    {
        return [
            QuotationStatus::Sent->value,
            QuotationStatus::Viewed->value,
            QuotationStatus::PendingApproval->value,
        ];
    }

    public function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    public function money(float $amount): string
    {
        return 'KES '.number_format($amount, 0);
    }

    public function baseQuery(CommercialQuotationReportScope $scope): Builder
    {
        $query = Quotation::query()
            ->where('quotations.company_id', $scope->companyId);

        if ($scope->branchId !== null) {
            $query->where('quotations.branch_id', $scope->branchId);
        }

        $query->whereDate('quotations.quotation_date', '>=', $scope->fromDate)
            ->whereDate('quotations.quotation_date', '<=', $scope->toDate);

        if ($scope->customerId !== null) {
            $query->where('quotations.customer_id', $scope->customerId);
        }

        if ($scope->salespersonId !== null) {
            $query->where('quotations.prepared_by', $scope->salespersonId);
        }

        if ($scope->status !== null && $scope->status !== '') {
            $query->where('quotations.status', $scope->status);
        }

        if ($scope->search !== '') {
            $term = '%'.$scope->search.'%';
            $query->where(function (Builder $inner) use ($term) {
                $inner->where('quotations.quotation_number', 'like', $term)
                    ->orWhereHas('customer', fn (Builder $customer) => $customer->where('company_name', 'like', $term));
            });
        }

        $this->applyExpiryFilter($query, $scope->expiryStatus);

        return $query;
    }

    protected function applyExpiryFilter(Builder $query, ?string $expiryStatus): void
    {
        if ($expiryStatus === null || $expiryStatus === '') {
            return;
        }

        $today = now()->toDateString();
        $soon = now()->addDays(7)->toDateString();

        match ($expiryStatus) {
            'expired' => $query->where(function (Builder $inner) use ($today) {
                $inner->where('quotations.status', QuotationStatus::Expired)
                    ->orWhere(function (Builder $nested) use ($today) {
                        $nested->whereNotIn('quotations.status', [
                            QuotationStatus::Accepted->value,
                            QuotationStatus::Converted->value,
                            QuotationStatus::Rejected->value,
                        ])
                            ->whereNotNull('quotations.valid_until')
                            ->whereDate('quotations.valid_until', '<', $today);
                    });
            }),
            'valid' => $query->where(function (Builder $inner) use ($today) {
                $inner->where('quotations.status', '!=', QuotationStatus::Expired)
                    ->where(function (Builder $nested) use ($today) {
                        $nested->whereNull('quotations.valid_until')
                            ->orWhereDate('quotations.valid_until', '>=', $today);
                    });
            }),
            'expiring_soon' => $query->whereIn('quotations.status', $this->openStatuses())
                ->whereNotNull('quotations.valid_until')
                ->whereDate('quotations.valid_until', '>=', $today)
                ->whereDate('quotations.valid_until', '<=', $soon),
            default => null,
        };
    }

    public function countIssued(CommercialQuotationReportScope $scope): int
    {
        if (! $this->hasTable('quotations')) {
            return 0;
        }

        return (int) $this->baseQuery($scope)
            ->whereIn('quotations.status', $this->issuedStatuses())
            ->count();
    }

    public function countByStatus(CommercialQuotationReportScope $scope, QuotationStatus|array $status): int
    {
        if (! $this->hasTable('quotations')) {
            return 0;
        }

        $statuses = is_array($status)
            ? array_map(fn (QuotationStatus $s) => $s->value, $status)
            : [$status->value];

        return (int) $this->baseQuery($scope)->whereIn('quotations.status', $statuses)->count();
    }

    public function sumTotalValue(CommercialQuotationReportScope $scope, ?array $statuses = null): float
    {
        if (! $this->hasTable('quotations')) {
            return 0.0;
        }

        $query = $this->baseQuery($scope);

        if ($statuses !== null) {
            $query->whereIn('quotations.status', $statuses);
        } else {
            $query->whereIn('quotations.status', $this->issuedStatuses());
        }

        return (float) $query->sum('quotations.total_amount');
    }

    public function averageQuoteValue(CommercialQuotationReportScope $scope): float
    {
        $count = $this->countIssued($scope);

        return $count > 0 ? $this->sumTotalValue($scope) / $count : 0.0;
    }

    public function acceptedQuoteValue(CommercialQuotationReportScope $scope): float
    {
        return $this->sumTotalValue($scope, [
            QuotationStatus::Accepted->value,
            QuotationStatus::Converted->value,
        ]);
    }

    public function conversionPercent(CommercialQuotationReportScope $scope): float
    {
        $issued = $this->countIssued($scope);
        if ($issued === 0) {
            return 0.0;
        }

        $won = $this->countByStatus($scope, [QuotationStatus::Accepted, QuotationStatus::Converted]);

        return round(($won / $issued) * 100, 1);
    }

    public function averageApprovalTimeHours(CommercialQuotationReportScope $scope): ?float
    {
        if (! $this->hasTable('quotations')) {
            return null;
        }

        $query = $this->baseQuery($scope)
            ->whereNotNull('quotations.approved_at')
            ->whereIn('quotations.status', [
                QuotationStatus::Accepted->value,
                QuotationStatus::Converted->value,
                QuotationStatus::Sent->value,
            ]);

        $avg = $this->isSqlite()
            ? $query->selectRaw('AVG((julianday(quotations.approved_at) - julianday(quotations.quotation_date)) * 24) as avg_hours')->value('avg_hours')
            : $query->selectRaw('AVG(TIMESTAMPDIFF(HOUR, quotations.quotation_date, quotations.approved_at)) as avg_hours')->value('avg_hours');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryMetrics(CommercialQuotationReportScope $scope): array
    {
        return [
            'issued' => $this->countIssued($scope),
            'accepted' => $this->countByStatus($scope, [QuotationStatus::Accepted, QuotationStatus::Converted]),
            'rejected' => $this->countByStatus($scope, QuotationStatus::Rejected),
            'expired' => $this->countExpired($scope),
            'open' => $this->countByStatus($scope, array_map(
                fn (string $s) => QuotationStatus::from($s),
                $this->openStatuses(),
            )),
            'total_value' => $this->sumTotalValue($scope),
            'average_value' => $this->averageQuoteValue($scope),
            'accepted_value' => $this->acceptedQuoteValue($scope),
            'conversion' => $this->conversionPercent($scope),
            'avg_approval_hours' => $this->averageApprovalTimeHours($scope),
        ];
    }

    public function countExpired(CommercialQuotationReportScope $scope): int
    {
        if (! $this->hasTable('quotations')) {
            return 0;
        }

        $today = now()->toDateString();

        return (int) $this->baseQuery($scope)
            ->where(function (Builder $query) use ($today) {
                $query->where('quotations.status', QuotationStatus::Expired)
                    ->orWhere(function (Builder $inner) use ($today) {
                        $inner->whereNotIn('quotations.status', [
                            QuotationStatus::Accepted->value,
                            QuotationStatus::Converted->value,
                            QuotationStatus::Rejected->value,
                        ])
                            ->whereNotNull('quotations.valid_until')
                            ->whereDate('quotations.valid_until', '<', $today);
                    });
            })
            ->count();
    }

    public function paginateQuotations(CommercialQuotationReportScope $scope, array|string|null $statusFilter = null): LengthAwarePaginator
    {
        if (! $this->hasTable('quotations')) {
            return $this->emptyPaginator($scope);
        }

        $query = $this->baseQuery($scope)
            ->with(['customer:id,company_name', 'preparer:id,name'])
            ->select([
                'quotations.id',
                'quotations.quotation_number',
                'quotations.customer_id',
                'quotations.prepared_by',
                'quotations.quotation_date',
                'quotations.valid_until',
                'quotations.total_amount',
                'quotations.status',
            ])
            ->orderByDesc('quotations.quotation_date');

        if ($statusFilter === 'open') {
            $query->whereIn('quotations.status', $this->openStatuses());
        } elseif ($statusFilter === 'expired') {
            $today = now()->toDateString();
            $query->where(function (Builder $inner) use ($today) {
                $inner->where('quotations.status', QuotationStatus::Expired)
                    ->orWhere(function (Builder $nested) use ($today) {
                        $nested->whereNotIn('quotations.status', [
                            QuotationStatus::Accepted->value,
                            QuotationStatus::Converted->value,
                            QuotationStatus::Rejected->value,
                        ])
                            ->whereNotNull('quotations.valid_until')
                            ->whereDate('quotations.valid_until', '<', $today);
                    });
            });
        } elseif (is_array($statusFilter)) {
            $query->whereIn('quotations.status', $statusFilter);
        } elseif ($statusFilter instanceof QuotationStatus) {
            $query->where('quotations.status', $statusFilter);
        }

        return $query->paginate(self::PER_PAGE, ['*'], 'page', $scope->page)
            ->through(fn (Quotation $quote) => [
                'quote' => $quote->quotation_number,
                'customer' => $quote->customer?->company_name ?? '—',
                'date' => $quote->quotation_date?->format('d M Y') ?? '—',
                'valid_until' => $quote->valid_until?->format('d M Y') ?? '—',
                'value' => $this->money((float) $quote->total_amount),
                'status' => ucfirst(str_replace('_', ' ', $quote->status->value)),
                'salesperson' => $quote->preparer?->name ?? '—',
            ]);
    }

    /**
     * @return list<array<string, string>>
     */
    public function valueAnalysis(CommercialQuotationReportScope $scope): array
    {
        if (! $this->hasTable('quotations')) {
            return [];
        }

        $buckets = [
            ['label' => __('Under KES 50,000'), 'min' => 0, 'max' => 50000],
            ['label' => __('KES 50,000 – 200,000'), 'min' => 50000, 'max' => 200000],
            ['label' => __('KES 200,000 – 500,000'), 'min' => 200000, 'max' => 500000],
            ['label' => __('KES 500,000+'), 'min' => 500000, 'max' => null],
        ];

        $rows = [];
        foreach ($buckets as $bucket) {
            $query = $this->baseQuery($scope)->whereIn('quotations.status', $this->issuedStatuses());
            $query->where('quotations.total_amount', '>=', $bucket['min']);
            if ($bucket['max'] !== null) {
                $query->where('quotations.total_amount', '<', $bucket['max']);
            }

            $count = (int) (clone $query)->count();
            $value = (float) (clone $query)->sum('quotations.total_amount');

            $rows[] = [
                'band' => $bucket['label'],
                'quotes' => (string) $count,
                'total_value' => $this->money($value),
                'average_value' => $this->money($count > 0 ? $value / $count : 0),
            ];
        }

        $stats = $this->baseQuery($scope)
            ->whereIn('quotations.status', $this->issuedStatuses())
            ->selectRaw('MIN(total_amount) as min_value, MAX(total_amount) as max_value, AVG(total_amount) as avg_value')
            ->first();

        $rows[] = [
            'band' => __('Overall range'),
            'quotes' => (string) $this->countIssued($scope),
            'total_value' => $this->money((float) ($stats->min_value ?? 0)).' – '.$this->money((float) ($stats->max_value ?? 0)),
            'average_value' => $this->money((float) ($stats->avg_value ?? 0)),
        ];

        return $rows;
    }

    /**
     * @return list<array<string, string>>
     */
    public function agingBuckets(CommercialQuotationReportScope $scope): array
    {
        if (! $this->hasTable('quotations')) {
            return [];
        }

        $today = now()->toDateString();

        $buckets = [
            ['label' => __('0–7 days'), 'min' => 0, 'max' => 7],
            ['label' => __('8–14 days'), 'min' => 8, 'max' => 14],
            ['label' => __('15–30 days'), 'min' => 15, 'max' => 30],
            ['label' => __('31–60 days'), 'min' => 31, 'max' => 60],
            ['label' => __('60+ days'), 'min' => 61, 'max' => null],
        ];

        $rows = [];
        foreach ($buckets as $bucket) {
            $query = $this->baseQuery($scope)
                ->whereIn('quotations.status', $this->openStatuses());

            if ($this->isSqlite()) {
                $query->whereRaw('CAST(julianday(?) - julianday(quotations.quotation_date) AS INTEGER) >= ?', [$today, $bucket['min']]);
                if ($bucket['max'] !== null) {
                    $query->whereRaw('CAST(julianday(?) - julianday(quotations.quotation_date) AS INTEGER) <= ?', [$today, $bucket['max']]);
                }
            } else {
                $query->whereRaw('DATEDIFF(?, quotations.quotation_date) >= ?', [$today, $bucket['min']]);
                if ($bucket['max'] !== null) {
                    $query->whereRaw('DATEDIFF(?, quotations.quotation_date) <= ?', [$today, $bucket['max']]);
                }
            }

            $count = (int) (clone $query)->count();
            $value = (float) (clone $query)->sum('quotations.total_amount');

            $rows[] = [
                'age_band' => $bucket['label'],
                'open_quotes' => (string) $count,
                'value' => $this->money($value),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function winRateAnalysis(CommercialQuotationReportScope $scope): array
    {
        $issued = $this->countIssued($scope);
        $accepted = $this->countByStatus($scope, QuotationStatus::Accepted);
        $converted = $this->countByStatus($scope, QuotationStatus::Converted);
        $rejected = $this->countByStatus($scope, QuotationStatus::Rejected);
        $expired = $this->countExpired($scope);
        $won = $accepted + $converted;

        return [
            'issued' => $issued,
            'won' => $won,
            'accepted' => $accepted,
            'converted' => $converted,
            'rejected' => $rejected,
            'expired' => $expired,
            'win_rate' => $issued > 0 ? round(($won / $issued) * 100, 1) : 0.0,
            'loss_rate' => $issued > 0 ? round((($rejected + $expired) / $issued) * 100, 1) : 0.0,
        ];
    }

    public function paginateByCustomer(CommercialQuotationReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('quotations')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->baseQuery($scope)
            ->whereIn('quotations.status', $this->issuedStatuses())
            ->select(
                'quotations.customer_id',
                DB::raw('COUNT(*) as quotes'),
                DB::raw('SUM(quotations.total_amount) as total_value'),
                DB::raw('AVG(quotations.total_amount) as average_value'),
                DB::raw('SUM(CASE WHEN quotations.status IN (\'accepted\', \'converted\') THEN 1 ELSE 0 END) as won'),
            )
            ->groupBy('quotations.customer_id')
            ->orderByDesc('total_value')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $names = Customer::query()
            ->whereIn('id', $paginator->getCollection()->pluck('customer_id'))
            ->pluck('company_name', 'id');

        return $paginator->through(function ($row) use ($names) {
            $quotes = (int) $row->quotes;
            $won = (int) $row->won;

            return [
                'customer' => $names[$row->customer_id] ?? '—',
                'quotes' => (string) $quotes,
                'total_value' => $this->money((float) $row->total_value),
                'average_value' => $this->money((float) $row->average_value),
                'win_rate' => $quotes > 0 ? round(($won / $quotes) * 100, 1).'%' : '0%',
            ];
        });
    }

    public function paginateBySalesperson(CommercialQuotationReportScope $scope): LengthAwarePaginator
    {
        if (! $this->hasTable('quotations')) {
            return $this->emptyPaginator($scope);
        }

        $paginator = $this->baseQuery($scope)
            ->whereIn('quotations.status', $this->issuedStatuses())
            ->select(
                'quotations.prepared_by',
                DB::raw('COUNT(*) as quotes'),
                DB::raw('SUM(quotations.total_amount) as total_value'),
                DB::raw('AVG(quotations.total_amount) as average_value'),
                DB::raw('SUM(CASE WHEN quotations.status IN (\'accepted\', \'converted\') THEN 1 ELSE 0 END) as won'),
            )
            ->groupBy('quotations.prepared_by')
            ->orderByDesc('total_value')
            ->paginate(self::PER_PAGE, ['*'], 'page', $scope->page);

        $names = User::query()
            ->whereIn('id', $paginator->getCollection()->pluck('prepared_by'))
            ->pluck('name', 'id');

        return $paginator->through(function ($row) use ($names) {
            $quotes = (int) $row->quotes;
            $won = (int) $row->won;

            return [
                'salesperson' => $names[$row->prepared_by] ?? '—',
                'quotes' => (string) $quotes,
                'total_value' => $this->money((float) $row->total_value),
                'average_value' => $this->money((float) $row->average_value),
                'win_rate' => $quotes > 0 ? round(($won / $quotes) * 100, 1).'%' : '0%',
            ];
        });
    }

    /**
     * @return list<array<string, string>>
     */
    public function branchBreakdown(CommercialQuotationReportScope $scope): array
    {
        if (! $this->hasTable('quotations')) {
            return [];
        }

        $rows = $this->baseQuery($scope)
            ->whereIn('quotations.status', $this->issuedStatuses())
            ->select(
                'quotations.branch_id',
                DB::raw('COUNT(*) as quotes'),
                DB::raw('SUM(quotations.total_amount) as total_value'),
                DB::raw('SUM(CASE WHEN quotations.status IN (\'accepted\', \'converted\') THEN 1 ELSE 0 END) as won'),
            )
            ->groupBy('quotations.branch_id')
            ->orderByDesc('total_value')
            ->get();

        $names = Branch::query()->whereIn('id', $rows->pluck('branch_id'))->pluck('name', 'id');

        return $rows->map(function ($row) use ($names) {
            $quotes = (int) $row->quotes;
            $won = (int) $row->won;

            return [
                'branch' => $names[$row->branch_id] ?? '—',
                'quotes' => (string) $quotes,
                'total_value' => $this->money((float) $row->total_value),
                'won' => (string) $won,
                'win_rate' => $quotes > 0 ? round(($won / $quotes) * 100, 1).'%' : '0%',
            ];
        })->all();
    }

    protected function emptyPaginator(CommercialQuotationReportScope $scope): LengthAwarePaginator
    {
        return new Paginator([], 0, self::PER_PAGE, $scope->page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    protected function isSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
}
