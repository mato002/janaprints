<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeExit;
use App\Support\Hr\EmployeeExitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExitDashboardController extends Controller
{
    public function __construct(
        protected EmployeeExitService $exits,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', EmployeeExit::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.exit.dashboard', [
            'stats' => $this->exits->dashboardStats($companyId),
            'recentExits' => EmployeeExit::query()
                ->forTenant()
                ->where('company_id', $companyId)
                ->with('employee')
                ->orderByDesc('initiated_at')
                ->limit(10)
                ->get(),
            'formData' => $this->exits->formData($companyId),
        ]);
    }
}
