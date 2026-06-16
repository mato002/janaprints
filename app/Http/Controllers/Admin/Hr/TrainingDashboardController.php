<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Support\Hr\TrainingAssignmentService;
use App\Support\Hr\TrainingProgramService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingDashboardController extends Controller
{
    public function __construct(
        protected TrainingAssignmentService $assignments,
        protected TrainingProgramService $programs,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.dashboard', [
            'stats' => array_merge(
                $this->assignments->dashboardStats($companyId),
                $this->programs->dashboardProgramStats($companyId),
            ),
            'expiring' => $this->assignments->expiringCertificates($companyId),
            'recentCompletions' => $this->assignments->recentCompletions($companyId),
            'upcomingScheduled' => $this->programs->upcomingScheduled($companyId),
            'skillsMatrix' => $this->assignments->skillsMatrix($companyId)->groupBy('employee_id'),
            'formData' => $this->assignments->formData($companyId),
        ]);
    }
}
