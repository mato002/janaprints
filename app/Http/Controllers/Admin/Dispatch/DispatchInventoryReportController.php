<?php

namespace App\Http\Controllers\Admin\Dispatch;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Services\Dispatch\DispatchInventoryReportService;
use Illuminate\View\View;

class DispatchInventoryReportController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected DispatchInventoryReportService $reports,
    ) {}

    public function transit(): View
    {
        abort_unless(auth()->user()?->can('dispatch.view'), 403);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return view('admin.dispatch.reports.transit-inventory', [
            'rows' => $this->reports->transitInventory($companyId, $branchId),
        ]);
    }

    public function cogs(): View
    {
        abort_unless(auth()->user()?->can('dispatch.view'), 403);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return view('admin.dispatch.reports.cogs-postings', [
            'notes' => $this->reports->cogsPostings($companyId, $branchId),
        ]);
    }
}
