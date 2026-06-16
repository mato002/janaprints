<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\TrainingProgram;
use App\Support\Hr\TrainingAssignmentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingCertificatesController extends Controller
{
    public function __construct(
        protected TrainingAssignmentService $assignments,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.certificates', [
            'certificates' => $this->assignments->paginateCertificates(
                $companyId,
                $request->only(['status', 'employee_id', 'program_id']),
            ),
            'filters' => $request->only(['status', 'employee_id', 'program_id']),
            'formData' => array_merge(
                $this->assignments->formData($companyId),
                [
                    'programs' => TrainingProgram::query()
                        ->where('company_id', $companyId)
                        ->orderBy('title')
                        ->get(),
                ],
            ),
        ]);
    }
}
