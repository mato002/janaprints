<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Services\Assets\MaintenanceWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceWorkspaceController extends Controller
{
    public function __invoke(Request $request, MaintenanceWorkspaceService $workspace): View
    {
        $this->authorize('viewAny', MaintenanceWorkOrder::class);

        return view('admin.assets.maintenance.hub', $workspace->build($request));
    }
}
