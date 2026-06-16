<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Support\Hr\HrDashboardCommandCenterService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrDashboardController extends Controller
{
    public function __construct(
        protected HrDashboardCommandCenterService $commandCenter,
    ) {}

    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('hr.dashboard.view'), 403);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.dashboard', [
            'dashboard' => $this->commandCenter->build($companyId, $request->user()),
        ]);
    }
}
