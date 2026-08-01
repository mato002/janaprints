<?php

namespace App\Support\Procurement;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\Vendor;
use Illuminate\Http\Request;

class BuyDeskPageBuilder
{
    use ResolvesProcurementTenant, ScopesToTenant;

    public function __construct(
        protected BuyDeskService $desk,
        protected BuyDeskWorkQueueService $workQueue,
        protected ProcurementApprovalQueueService $approvalQueue,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $user = $request->user();
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $activeView = ProcurementDeskViews::normalize($request->query('view'));

        $payload = [
            'activeProcurementView' => $activeView,
            'fullSupplyChainDeskUrl' => route('admin.workspaces.supply-chain.section', [
                'section' => 'procurement',
                'tab' => 'buy-desk',
            ]),
        ];

        if (ProcurementDeskViews::isPanelView($activeView)) {
            return array_merge($payload, $this->panelPayload($activeView, $request, $companyId, $branchId));
        }

        $workQueue = $this->workQueue->present($request, $companyId, $branchId);

        return array_merge($payload, [
            'workQueue' => $workQueue,
            'fastActions' => $this->desk->fastActions($user),
            'pipelineStages' => $this->desk->pipelineStages($workQueue['counts'] ?? []),
            'receivingPipeline' => $this->desk->receivingPipeline($user),
            'queueItems' => $workQueue['items'] ?? [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function panelPayload(string $view, Request $request, int $companyId, ?int $branchId): array
    {
        return match ($view) {
            ProcurementDeskViews::REQUESTS => [
                'registerTitle' => __('Purchase Requests'),
                'requests' => $this->scopeToTenant(
                    PurchaseRequest::query()->with(['requester', 'department'])->latest()
                )->paginate(config('platform.pagination.default', 15))->withQueryString(),
            ],
            ProcurementDeskViews::SUPPLIERS => [
                'registerTitle' => __('Vendors'),
                'registerDescription' => __('Supplier and vendor master data.'),
                'vendors' => $this->scopeToTenant(
                    Vendor::query()->latest('vendor_name')
                )->paginate(config('platform.pagination.default', 15))->withQueryString(),
            ],
            ProcurementDeskViews::RFQS => [
                'registerTitle' => __('Requests For Quotation'),
                'rfqs' => $this->scopeToTenant(
                    Rfq::query()->with(['purchaseRequest', 'awardedVendor'])->latest()
                )->paginate(config('platform.pagination.default', 15))->withQueryString(),
            ],
            ProcurementDeskViews::ORDERS => [
                'registerTitle' => __('Purchase Orders'),
                'orders' => $this->scopeToTenant(
                    PurchaseOrder::query()->with(['vendor'])->latest('order_date')
                )->paginate(config('platform.pagination.default', 15))->withQueryString(),
            ],
            ProcurementDeskViews::RECEIPTS => [
                'registerTitle' => __('Goods Receipts'),
                'receipts' => $this->scopeToTenant(
                    GoodsReceipt::query()->with(['purchaseOrder.vendor', 'receiver'])->latest('receipt_date')
                )->paginate(config('platform.pagination.default', 15))->withQueryString(),
            ],
            ProcurementDeskViews::APPROVALS => $this->approvalsPayload($request, $companyId, $branchId),
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function approvalsPayload(Request $request, int $companyId, ?int $branchId): array
    {
        abort_unless($request->user()?->can('procurement.approvals.view'), 403);

        return [
            'registerTitle' => __('Procurement approvals'),
            'registerDescription' => __('Pending, aging, escalated, and rejected procurement approval chains.'),
            'sections' => $this->approvalQueue->present($companyId, $branchId, $request->user()),
        ];
    }
}
