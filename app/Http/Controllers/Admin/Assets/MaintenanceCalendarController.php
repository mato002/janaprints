<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\MaintenanceWorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaintenanceCalendarController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', MaintenanceWorkOrder::class);
        abort_unless($request->user()?->can('maintenance.calendar.view'), 403);

        return redirect()->route('admin.assets.maintenance.dashboard', array_merge(
            $request->query(),
            ['tab' => 'calendar'],
        ));
    }
}
