<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use App\Http\Controllers\Controller;
use App\Models\Governance\ApprovalChainRun;
use App\Support\Procurement\ProcurementApprovalActionService;
use App\Support\Procurement\ProcurementApprovalQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProcurementApprovalQueueController extends Controller
{
    use ResolvesProcurementTenant;

    public function __construct(
        protected ProcurementApprovalQueueService $queue,
        protected ProcurementApprovalActionService $actions,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('procurement.approvals.view'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        return view('admin.procurement.approvals.index', [
            'sections' => $this->queue->present($companyId, $branchId, $request->user()),
        ]);
    }

    public function approve(Request $request, ApprovalChainRun $run): RedirectResponse
    {
        abort_unless($request->user()?->can('procurement.approvals.view'), 403);
        $this->ensureTenantRun($run);

        try {
            $this->actions->approve($run, $request->user(), $request->input('notes'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Approval recorded.'));
    }

    public function reject(Request $request, ApprovalChainRun $run): RedirectResponse
    {
        abort_unless($request->user()?->can('procurement.approvals.view'), 403);
        $this->ensureTenantRun($run);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->actions->reject($run, $request->user(), $validated['reason']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Approval rejected.'));
    }

    protected function ensureTenantRun(ApprovalChainRun $run): void
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds(request());

        abort_unless(
            $run->company_id === $companyId && ($branchId === null || $run->branch_id === $branchId),
            404,
        );
    }
}
