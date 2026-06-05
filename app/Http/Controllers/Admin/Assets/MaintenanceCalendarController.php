<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\MaintenanceWorkOrder;
use App\Services\Assets\MaintenanceCalendarService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceCalendarController extends Controller
{
    public function __invoke(Request $request, MaintenanceCalendarService $calendar): View
    {
        $this->authorize('viewAny', MaintenanceWorkOrder::class);
        abort_unless($request->user()?->can('maintenance.calendar.view'), 403);

        $view = $request->query('view', 'month');

        return view('admin.assets.maintenance.calendar', [
            'calendar' => $calendar->build(
                (int) tenant()->companyId(),
                tenant()->branchId(),
                in_array($view, ['month', 'week', 'upcoming', 'overdue'], true) ? $view : 'month',
                $request->query('date'),
            ),
            'activeView' => $view,
        ]);
    }
}
