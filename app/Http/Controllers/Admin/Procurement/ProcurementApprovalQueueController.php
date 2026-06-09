<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Support\Procurement\ProcurementApprovalQueueService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementApprovalQueueController extends Controller
{
    use ResolvesProcurementTenant;

    public function __construct(
        protected ProcurementApprovalQueueService $queue,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('procurement.approvals.view'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        return view('admin.procurement.approvals.index', [
            'sections' => $this->queue->present($companyId, $branchId),
        ]);
    }
}
