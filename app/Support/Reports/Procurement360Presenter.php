<?php

namespace App\Support\Reports;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Enums\VendorStatus;
use App\Models\Branch;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\Vendor;
use App\Support\Reports\Concerns\BuildsIntelligenceSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Procurement360Presenter
{
    use BuildsIntelligenceSections;

    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
        protected IntelligenceAggregateQueries $queries,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Request $request): array
    {
        $resolved = $this->scopeResolver->resolve($request, includeVendor: true);
        $scope = $resolved['scope'];

        $vendors = $this->queries->hasTable('vendors')
            ? Vendor::query()
                ->where('company_id', $scope->companyId)
                ->where('status', VendorStatus::Active)
                ->orderBy('vendor_name')
                ->get(['id', 'vendor_name'])
            : collect();

        return [
            'title' => __('Procurement 360'),
            'description' => __('Purchasing pipeline, vendors, and goods receipt intelligence.'),
            'filters' => $resolved['filters'],
            'branches' => $resolved['branches'],
            'vendors' => $vendors,
            'can_export' => $resolved['can_export'],
            'export_route' => 'admin.reports.intelligence360.export',
            'export_route_params' => ['reportKey' => 'procurement'],
            'sections' => [
                $this->summary($scope),
                $this->rfqSection($scope),
                $this->vendorPerformance($scope),
                $this->purchaseOrderAnalysis($scope),
                $this->goodsReceiptAnalysis($scope),
                $this->pipeline($scope),
                $this->branchProcurement($scope),
                $this->attention($scope),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('purchase_requests')) {
            return $this->pendingSection(__('Procurement Summary'));
        }

        $q = fn () => $this->queries->scoped(PurchaseRequest::class, $scope);
        $po = fn () => $this->queries->scoped(PurchaseOrder::class, $scope);

        return $this->kpiSection(__('Procurement Summary'), [
            $this->kpi(__('Open PRs'), (string) $q()->where('status', PurchaseRequestStatus::Draft)->count(), 'document-text'),
            $this->kpi(__('Submitted PRs'), (string) $q()->where('status', PurchaseRequestStatus::Submitted)->count(), 'inbox'),
            $this->kpi(__('Approved PRs'), (string) $q()->where('status', PurchaseRequestStatus::Approved)->count(), 'check-circle'),
            $this->kpi(__('Pending POs'), (string) $this->queries->countPendingPurchaseOrders($scope), 'clipboard-list'),
            $this->kpi(__('Sent POs'), (string) $po()->where('status', PurchaseOrderStatus::Sent)->count(), 'truck'),
            $this->kpi(__('Goods Awaiting Receipt'), (string) $this->queries->countGoodsAwaitingReceipt($scope), 'inbox'),
            $this->kpi(__('Procurement Value'), $this->queries->money($this->queries->sumProcurementValueInPeriod($scope)), 'currency-dollar'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rfqSection(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('rfqs')) {
            return $this->pendingSection(__('RFQ Intelligence'));
        }

        $base = $this->queries->scoped(Rfq::class, $scope);

        return $this->kpiSection(__('RFQ Intelligence'), [
            $this->kpi(__('Open RFQs'), (string) (clone $base)->where('status', RfqStatus::Open)->count(), 'document-text'),
            $this->kpi(__('RFQs Closing Soon'), '—', 'clock', __('Pending source')),
            $this->kpi(__('Vendor Responses'), '—', 'inbox', __('Pending source')),
            $this->kpi(__('Awarded RFQs'), '—', 'badge-check', __('Pending source')),
            $this->kpi(__('Converted RFQs'), '—', 'check-circle', __('Pending source')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function vendorPerformance(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('vendors')) {
            return $this->pendingSection(__('Vendor Performance'));
        }

        $active = $this->queries->countVendors($scope);
        $used = $this->queries->hasTable('purchase_orders')
            ? (int) $this->queries->scoped(PurchaseOrder::class, $scope)
                ->whereDate('order_date', '>=', $scope->fromDate)
                ->whereDate('order_date', '<=', $scope->toDate)
                ->distinct('vendor_id')
                ->count('vendor_id')
            : 0;

        $top = $this->queries->hasTable('purchase_orders')
            ? $this->queries->scoped(PurchaseOrder::class, $scope)
                ->whereDate('order_date', '>=', $scope->fromDate)
                ->whereDate('order_date', '<=', $scope->toDate)
                ->select('vendor_id', DB::raw('SUM(total_amount) as value'))
                ->groupBy('vendor_id')
                ->orderByDesc('value')
                ->limit(5)
                ->get()
            : collect();

        $vendorNames = Vendor::query()->whereIn('id', $top->pluck('vendor_id'))->pluck('vendor_name', 'id');

        return [
            'type' => 'split',
            'title' => __('Vendor Performance'),
            'kpis' => [
                $this->kpi(__('Active Vendors'), (string) $active, 'truck'),
                $this->kpi(__('Vendors Used (period)'), (string) $used, 'user-circle'),
                $this->kpi(__('On-Time Receipt'), '—', 'clock', __('Pending source')),
                $this->kpi(__('Vendor Comparison'), '—', 'scale', __('Pending source')),
            ],
            'tables' => [
                $this->tableSection(
                    __('Purchase Value by Vendor'),
                    [__('Vendor'), __('Value')],
                    $top->map(fn ($r) => [
                        'name' => $vendorNames[$r->vendor_id] ?? __('Vendor'),
                        'value' => $this->queries->money((float) $r->value),
                    ])->all(),
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function purchaseOrderAnalysis(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('purchase_orders')) {
            return $this->pendingSection(__('Purchase Order Analysis'));
        }

        $byStatus = $this->queries->scoped(PurchaseOrder::class, $scope)
            ->select('status', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(total_amount) as value'))
            ->groupBy('status')
            ->get();

        $rows = $byStatus->map(fn ($r) => [
            'status' => (string) $r->status,
            'count' => (string) $r->cnt,
            'value' => $this->queries->money((float) $r->value),
        ])->all();

        $awaitingApproval = (int) $this->queries->scoped(PurchaseOrder::class, $scope)
            ->where('status', PurchaseOrderStatus::PendingApproval)->count();
        $partial = (int) $this->queries->scoped(PurchaseOrder::class, $scope)
            ->where('status', PurchaseOrderStatus::PartiallyReceived)->count();
        $overdue = (int) $this->queries->scoped(PurchaseOrder::class, $scope)
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', $scope->toDate)
            ->whereIn('status', [PurchaseOrderStatus::Sent, PurchaseOrderStatus::PartiallyReceived])
            ->count();

        return [
            'type' => 'split',
            'title' => __('Purchase Order Analysis'),
            'kpis' => [
                $this->kpi(__('POs Awaiting Approval'), (string) $awaitingApproval, 'document-text'),
                $this->kpi(__('Partially Received'), (string) $partial, 'inbox'),
                $this->kpi(__('Overdue POs'), (string) $overdue, 'exclamation'),
            ],
            'tables' => [
                $this->tableSection(__('PO by Status'), [__('Status'), __('Count'), __('Value')], $rows),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function goodsReceiptAnalysis(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('goods_receipts')) {
            return $this->pendingSection(__('Goods Receipt Analysis'));
        }

        return $this->kpiSection(__('Goods Receipt Analysis'), [
            $this->kpi(__('Received (period)'), (string) $this->queries->countGoodsReceiptsInPeriod($scope), 'inbox'),
            $this->kpi(__('Draft Receipts'), (string) $this->queries->countGoodsReceiptsInPeriod($scope, GoodsReceiptStatus::Draft), 'document-text'),
            $this->kpi(__('Posted Receipts'), (string) $this->queries->countGoodsReceiptsInPeriod($scope, GoodsReceiptStatus::Posted), 'check-circle'),
            $this->kpi(__('Receipt Value'), '—', 'currency-dollar', __('Pending source')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function pipeline(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('purchase_requests')) {
            return $this->pendingSection(__('Procurement Pipeline'));
        }

        $stages = [
            ['label' => __('Purchase Request'), 'count' => (int) $this->queries->scoped(PurchaseRequest::class, $scope)->count()],
            ['label' => __('Approved'), 'count' => (int) $this->queries->scoped(PurchaseRequest::class, $scope)->where('status', PurchaseRequestStatus::Approved)->count()],
            ['label' => __('PO Created'), 'count' => $this->queries->hasTable('purchase_orders') ? (int) $this->queries->scoped(PurchaseOrder::class, $scope)->count() : 0],
            ['label' => __('PO Sent'), 'count' => $this->queries->hasTable('purchase_orders') ? (int) $this->queries->scoped(PurchaseOrder::class, $scope)->where('status', PurchaseOrderStatus::Sent)->count() : 0],
            ['label' => __('Goods Received'), 'count' => $this->queries->hasTable('goods_receipts') ? $this->queries->countGoodsReceiptsInPeriod($scope) : 0],
            ['label' => __('Closed'), 'count' => $this->queries->hasTable('purchase_orders') ? (int) $this->queries->scoped(PurchaseOrder::class, $scope)->where('status', PurchaseOrderStatus::Closed)->count() : 0],
        ];

        return ['type' => 'pipeline', 'title' => __('Procurement Pipeline'), 'stages' => $stages];
    }

    /**
     * @return array<string, mixed>
     */
    protected function branchProcurement(IntelligenceScope $scope): array
    {
        if (! $this->queries->hasTable('purchase_orders')) {
            return $this->pendingSection(__('Branch Procurement'));
        }

        $rows = Branch::query()
            ->where('company_id', $scope->companyId)
            ->where('is_active', true)
            ->when($scope->branchId, fn ($q) => $q->where('id', $scope->branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (Branch $branch) use ($scope) {
                $pr = $this->queries->hasTable('purchase_requests')
                    ? (int) PurchaseRequest::query()->where('company_id', $scope->companyId)->where('branch_id', $branch->id)->count()
                    : 0;
                $po = (int) PurchaseOrder::query()->where('company_id', $scope->companyId)->where('branch_id', $branch->id)->count();
                $value = (float) PurchaseOrder::query()
                    ->where('company_id', $scope->companyId)
                    ->where('branch_id', $branch->id)
                    ->whereDate('order_date', '>=', $scope->fromDate)
                    ->whereDate('order_date', '<=', $scope->toDate)
                    ->sum('total_amount');

                return [
                    'branch' => $branch->name,
                    'prs' => (string) $pr,
                    'pos' => (string) $po,
                    'receipts' => '—',
                    'value' => $this->queries->money($value),
                    'pending' => (string) $this->queries->countPendingPurchaseOrders(new IntelligenceScope($scope->companyId, $branch->id, $scope->fromDate, $scope->toDate)),
                ];
            })
            ->all();

        return $this->tableSection(
            __('Branch Procurement'),
            [__('Branch'), __('PRs'), __('POs'), __('Receipts'), __('Value'), __('Pending')],
            $rows,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function attention(IntelligenceScope $scope): array
    {
        $items = [];

        if ($this->queries->hasTable('purchase_requests')) {
            $items[] = ['label' => __('PRs awaiting approval'), 'count' => (int) $this->queries->scoped(PurchaseRequest::class, $scope)->where('status', PurchaseRequestStatus::Submitted)->count(), 'severity' => 'warning'];
        }

        if ($this->queries->hasTable('purchase_orders')) {
            $items[] = ['label' => __('POs awaiting approval'), 'count' => (int) $this->queries->scoped(PurchaseOrder::class, $scope)->where('status', PurchaseOrderStatus::PendingApproval)->count(), 'severity' => 'warning'];
            $items[] = ['label' => __('POs awaiting receipt'), 'count' => $this->queries->countGoodsAwaitingReceipt($scope), 'severity' => 'danger'];
        }

        return ['type' => 'attention', 'title' => __('Attention Center'), 'items' => $items];
    }
}
