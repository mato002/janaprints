<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Support\Hr\TrainingProgramService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingCalendarController extends Controller
{
    public function __construct(
        protected TrainingProgramService $programs,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $year = (int) $request->input('year', now()->year);
        $month = max(1, min(12, (int) $request->input('month', now()->month)));

        return view('admin.hr.training.calendar', [
            'programs' => $this->programs->calendar($companyId, $year, $month, $request->only(['status', 'type'])),
            'filters' => $request->only(['status', 'type']),
            'year' => $year,
            'month' => $month,
        ]);
    }
}
