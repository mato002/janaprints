<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Services\Production\ProductionSchedulingWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionSchedulingController extends Controller
{
    public function index(Request $request, ProductionSchedulingWorkspaceService $workspace): View
    {
        $this->authorize('viewSchedulingWorkspace', ProductionJobCard::class);

        $viewMode = $request->query('view') === 'calendar' ? 'calendar' : 'list';
        $month = $request->query('month', now()->format('Y-m'));

        return view('admin.production.scheduling.index', [
            'kpis' => $workspace->kpiCounts(),
            'workCenterLoad' => $workspace->workCenterLoadPanel(),
            'warnings' => $workspace->schedulingWarnings(),
            'jobs' => $viewMode === 'list' ? $workspace->paginatedIndex($request) : null,
            'calendar' => $viewMode === 'calendar' ? $workspace->calendarMonth($request) : null,
            'workCenters' => WorkCenter::query()->forTenant()->orderBy('name')->get(['id', 'name']),
            'statuses' => ProductionJobCardStatus::cases(),
            'priorities' => ProductionPriority::cases(),
            'filters' => [
                'status' => $request->query('status'),
                'priority' => $request->query('priority'),
                'work_center_id' => $request->query('work_center_id'),
                'date' => $request->query('date'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'search' => $request->query('search'),
                'view' => $viewMode,
                'month' => $month,
            ],
            'viewMode' => $viewMode,
            'workspace' => $workspace,
        ]);
    }
}
