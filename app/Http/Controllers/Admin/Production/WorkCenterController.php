<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\WorkCenter;
use App\Services\Production\WorkCenterWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkCenterController extends Controller
{
    public function index(Request $request, WorkCenterWorkspaceService $workspace): View
    {
        $this->authorize('viewAny', WorkCenter::class);

        $dashboard = $workspace->build($request);

        return view('admin.production.work-centers.index', [
            'dashboard' => $dashboard,
            'workspace' => $workspace,
            'filters' => $dashboard['filters'],
            'filterOptions' => $dashboard['filter_options'],
            'activeChips' => $dashboard['active_filter_chips'],
        ]);
    }

    public function show(WorkCenter $workCenter, WorkCenterWorkspaceService $workspace): View
    {
        $this->authorize('view', $workCenter);

        $detail = $workspace->buildShow($workCenter);

        return view('admin.production.work-centers.show', [
            'workCenter' => $workCenter,
            'detail' => $detail,
            'metrics' => $detail['metrics'],
            'activeQueues' => $detail['active_queues'],
            'defaultCapacity' => $detail['default_capacity'],
        ]);
    }
}
