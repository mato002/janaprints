<?php

namespace App\Http\Controllers\Admin\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Http\Controllers\Controller;
use App\Models\Dispatch\DeliveryNote;
use App\Services\Dispatch\DeliveryCalendarService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryCalendarController extends Controller
{
    public function __invoke(Request $request, DeliveryCalendarService $calendar): View
    {
        $this->authorize('viewAny', DeliveryNote::class);

        return view('admin.dispatch.calendar', [
            'calendar' => $calendar->calendarMonth($request),
            'statuses' => DeliveryNoteStatus::cases(),
            'filterStatus' => $request->query('status'),
        ]);
    }
}
