<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Support\Hr\TrainingAssignmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingDashboardController extends Controller
{
    public function __construct(
        protected TrainingAssignmentService $assignments,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.dashboard', [
            'stats' => $this->assignments->dashboardStats($companyId),
            'expiring' => $this->assignments->expiringCertificates($companyId),
            'skillsMatrix' => $this->assignments->skillsMatrix($companyId)->groupBy('employee_id'),
            'formData' => $this->assignments->formData($companyId),
        ]);
    }
}
