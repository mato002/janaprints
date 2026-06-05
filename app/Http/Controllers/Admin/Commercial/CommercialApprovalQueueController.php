<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Support\Commercial\CommercialApprovalQueueService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialApprovalQueueController extends Controller
{
    use ResolvesCrmTenant;

    public function __construct(
        protected CommercialApprovalQueueService $queue,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.approvals.view'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $sections = $this->queue->present($companyId, $branchId);

        return view('admin.commercial.approvals.index', [
            'sections' => $sections,
            'canAction' => $request->user()->can('commercial.approvals.action'),
            'canApproveQuotations' => $request->user()->can('quotations.approve'),
            'canRejectQuotations' => $request->user()->can('quotations.edit'),
            'canConfirmOrders' => $request->user()->can('sales_orders.confirm'),
            'canApproveArtwork' => $request->user()->can('artwork.approve'),
        ]);
    }
}
