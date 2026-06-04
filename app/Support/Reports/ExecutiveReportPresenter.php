<?php

namespace App\Support\Reports;

use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\ActivityLog;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExecutiveReportPresenter
{
    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request);
        $scope = $resolved['scope'];

        return [
            'title' => __('Executive Dashboard'),
            'description' => __('Cross-module executive summary for leadership review.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'can_export' => $resolved['can_export'],
            'widget_sections' => $this->widgetSections($scope),
            'attention' => $this->attentionCenter($scope),
            'pipeline' => $this->queries->hasTable('production_job_cards')
                ? $this->productionPipeline($scope)
                : [],
            'branches_table' => $this->branchPerformance($scope),
            'recent_activity' => $this->recentActivity($scope),
        ];
    }

    /**
     * @return list<array{title: string, widgets: list<array<string, mixed>>}>
     */
    protected function widgetSections(IntelligenceScope $scope): array
    {
        $openQuoteStatuses = [
            QuotationStatus::Draft,
            QuotationStatus::PendingApproval,
            QuotationStatus::Sent,
            QuotationStatus::Viewed,
        ];

        $commercial = [];
        if ($this->queries->hasTable('quotations')) {
            $commercial[] = $this->metric(__('Open quotations'), (string) $this->queries->countQuotations($scope, $openQuoteStatuses), 'document-text');
            $commercial[] = $this->metric(
                __('Quotations sent this month'),
                (string) $this->queries->countQuotationsInPeriod($scope, [QuotationStatus::Sent, QuotationStatus::Viewed, QuotationStatus::Accepted]),
                'document-text',
            );
        } else {
            $commercial[] = $this->pending(__('Open quotations'), 'document-text');
            $commercial[] = $this->pending(__('Quotations sent this month'), 'document-text');
        }

        $commercial[] = $this->queries->hasTable('customers')
            ? $this->metric(__('Customers count'), (string) $this->queries->countCustomers($scope), 'user-circle')
            : $this->pending(__('Customers count'), 'user-circle');

        $commercial[] = $this->queries->hasTable('leads')
            ? $this->metric(__('Leads count'), (string) $this->queries->countLeads($scope), 'sparkles')
            : $this->pending(__('Leads count'), 'sparkles');

        $production = [];
        if ($this->queries->hasTable('production_job_cards')) {
            $production[] = $this->metric(__('Active jobs'), (string) $this->queries->countActiveJobs($scope), 'cog');
            $production[] = $this->metric(__('Completed jobs this month'), (string) $this->queries->countCompletedJobsInPeriod($scope), 'inbox');
            $production[] = $this->metric(__('Delayed jobs'), (string) $this->queries->countDelayedJobs($scope), 'bell');
        } else {
            $production[] = $this->pending(__('Active jobs'), 'cog');
            $production[] = $this->pending(__('Completed jobs this month'), 'inbox');
            $production[] = $this->pending(__('Delayed jobs'), 'bell');
        }

        $supplyChain = [];
        $invValue = $this->queries->inventoryValue($scope);
        if ($invValue !== null) {
            $supplyChain[] = $this->metric(__('Inventory value'), $this->queries->money($invValue), 'cube');
            $supplyChain[] = $this->metric(__('Low stock alerts'), (string) $this->queries->countLowStockAlerts($scope), 'bell');
        } else {
            $supplyChain[] = $this->pending(__('Inventory value'), 'cube');
            $supplyChain[] = $this->pending(__('Low stock alerts'), 'bell');
        }

        $supplyChain[] = $this->queries->hasTable('purchase_requests')
            ? $this->metric(__('Pending purchase requests'), (string) $this->queries->countPendingPurchaseRequests($scope), 'clipboard-list')
            : $this->pending(__('Pending purchase requests'), 'clipboard-list');

        $supplyChain[] = $this->queries->hasTable('purchase_orders')
            ? $this->metric(__('Pending purchase orders'), (string) $this->queries->countPendingPurchaseOrders($scope), 'truck')
            : $this->pending(__('Pending purchase orders'), 'truck');

        $supplyChain[] = $this->queries->hasTable('purchase_orders')
            ? $this->metric(__('Goods awaiting receipt'), (string) $this->queries->countGoodsAwaitingReceipt($scope), 'inbox')
            : $this->pending(__('Goods awaiting receipt'), 'inbox');

        $accounting = [];
        $receivables = $this->queries->sumReceivables($scope);
        $accounting[] = $receivables !== null
            ? $this->metric(__('Receivables'), $this->queries->money($receivables), 'cash')
            : $this->pending(__('Receivables'), 'cash');

        $payables = $this->queries->sumPayables($scope);
        $accounting[] = $payables !== null
            ? $this->metric(__('Payables'), $this->queries->money($payables), 'currency-dollar')
            : $this->pending(__('Payables'), 'currency-dollar');

        $revenue = $this->queries->sumRevenueMtd($scope);
        $accounting[] = $revenue !== null
            ? $this->metric(__('Revenue MTD'), $this->queries->money($revenue), 'chart-pie')
            : $this->pending(__('Revenue MTD'), 'chart-pie');

        return [
            ['title' => __('Commercial'), 'widgets' => $commercial],
            ['title' => __('Production'), 'widgets' => $production],
            ['title' => __('Supply Chain'), 'widgets' => $supplyChain],
            ['title' => __('Accounting'), 'widgets' => $accounting],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function productionPipeline(IntelligenceScope $scope): array
    {
        $counts = $this->queries->productionPipelineCounts($scope);
        $max = max(1, ...array_column($counts, 'count'));

        return array_map(function (array $row) use ($max) {
            $row['percent'] = (int) round(($row['count'] / $max) * 100);

            return $row;
        }, $counts);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function attentionCenter(IntelligenceScope $scope): array
    {
        $items = [];

        if ($this->queries->hasTable('production_job_cards')) {
            $items[] = [
                'label' => __('Delayed Jobs'),
                'count' => $this->queries->countDelayedJobs($scope),
                'severity' => 'danger',
                'route' => 'admin.production.job-cards.index',
            ];
        }

        if ($this->queries->hasTable('artwork_requests')) {
            $items[] = [
                'label' => __('Pending Artwork Approvals'),
                'count' => (int) $this->queries->scoped(ArtworkRequest::class, $scope)
                    ->where('status', ArtworkRequestStatus::Submitted)->count(),
                'severity' => 'warning',
                'route' => 'admin.artwork.requests.index',
            ];
        }

        if ($this->queries->hasTable('quotations')) {
            $items[] = [
                'label' => __('Pending Quotations'),
                'count' => (int) $this->queries->scoped(Quotation::class, $scope)
                    ->where('status', QuotationStatus::PendingApproval)->count(),
                'severity' => 'warning',
                'route' => 'admin.quotations.index',
            ];
        }

        if ($this->queries->hasTable('inventory_reorder_alerts')) {
            $items[] = [
                'label' => __('Stock Alerts'),
                'count' => $this->queries->countLowStockAlerts($scope),
                'severity' => 'danger',
                'route' => 'admin.inventory.dashboard',
            ];
        }

        if ($this->queries->hasTable('purchase_requests')) {
            $items[] = [
                'label' => __('PRs Awaiting Approval'),
                'count' => (int) $this->queries->scoped(PurchaseRequest::class, $scope)
                    ->where('status', PurchaseRequestStatus::Submitted)->count(),
                'severity' => 'warning',
                'route' => 'admin.procurement.requests.index',
            ];
        }

        $receivables = $this->queries->sumReceivables($scope);
        $items[] = [
            'label' => __('Outstanding Receivables'),
            'count' => null,
            'display' => $receivables !== null ? $this->queries->money($receivables) : '—',
            'severity' => 'muted',
            'hint' => $receivables === null ? __('Module not ready') : null,
            'route' => $receivables !== null ? 'admin.invoices.index' : null,
        ];

        return $items;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function branchPerformance(IntelligenceScope $scope): array
    {
        $branches = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn ($q) => $q->where('id', $scope->branchId))
            ->orderBy('name')
            ->get(['id', 'name']);

        if (! $this->queries->hasTable('sales_orders')) {
            return $branches->map(fn (Branch $b) => [
                'name' => $b->name,
                'sales' => '—',
                'jobs' => '—',
                'receivables' => '—',
                'profit' => '—',
                'pending' => true,
            ])->all();
        }

        $salesByBranch = SalesOrder::query()
            ->where('company_id', $scope->companyId)
            ->whereDate('order_date', '>=', $scope->fromDate)
            ->whereDate('order_date', '<=', $scope->toDate)
            ->whereNotIn('status', [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled])
            ->select('branch_id', DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $jobsByBranch = $this->queries->hasTable('production_job_cards')
            ? ProductionJobCard::query()
                ->where('company_id', $scope->companyId)
                ->whereDate('created_at', '>=', $scope->fromDate)
                ->whereDate('created_at', '<=', $scope->toDate)
                ->select('branch_id', DB::raw('COUNT(*) as jobs'))
                ->groupBy('branch_id')
                ->get()
                ->keyBy('branch_id')
            : collect();

        return $branches->map(function (Branch $branch) use ($salesByBranch, $jobsByBranch) {
            $sales = $salesByBranch->get($branch->id);

            return [
                'name' => $branch->name,
                'sales' => $this->queries->money((float) ($sales->revenue ?? 0)),
                'sales_raw' => (float) ($sales->revenue ?? 0),
                'jobs' => (int) ($jobsByBranch->get($branch->id)?->jobs ?? 0),
                'receivables' => '—',
                'profit' => '—',
                'pending' => false,
            ];
        })->sortByDesc('sales_raw')->values()->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function recentActivity(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('activity_logs')) {
            return [];
        }

        return ActivityLog::query()
            ->where('company_id', $scope->companyId)
            ->with('user:id,name')
            ->latest('created_at')
            ->limit(10)
            ->get(['id', 'user_id', 'action', 'model_type', 'model_id', 'created_at'])
            ->map(fn ($log) => [
                'message' => trim(($log->user?->name ?? __('System')).' '.$log->action.' '.class_basename((string) $log->model_type)),
                'created_at' => $log->created_at?->diffForHumans(),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function metric(string $label, string $value, string $icon): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'icon' => $icon,
            'hint' => null,
            'pending' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function pending(string $label, string $icon): array
    {
        return [
            'label' => $label,
            'value' => '—',
            'icon' => $icon,
            'hint' => __('Module not ready'),
            'pending' => true,
        ];
    }
}
