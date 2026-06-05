<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Support\Hr\TrainingAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainingAssignmentController extends Controller
{
    public function __construct(
        protected TrainingAssignmentService $assignments,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.assignments.index', [
            'assignments' => $this->assignments->paginate($companyId, $request->only([
                'employee_id', 'status', 'type',
            ])),
            'filters' => $request->only(['employee_id', 'status', 'type']),
            'formData' => $this->assignments->formData($companyId),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.assignments.create', [
            'formData' => $this->assignments->formData($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'training_program_id' => [
                'required',
                Rule::exists('training_programs', 'id')->where('company_id', $companyId),
            ],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $assignment = $this->assignments->assign($companyId, $validated, $request->user());

        return redirect()
            ->route('admin.hr.training.assignments.show', $assignment)
            ->with('status', __('Training assigned.'));
    }

    public function show(EmployeeTrainingAssignment $employeeTrainingAssignment): View
    {
        $this->authorize('view', $employeeTrainingAssignment);

        $employeeTrainingAssignment->load(['employee', 'program', 'assignedBy', 'completedBy', 'skills']);

        return view('admin.hr.training.assignments.show', [
            'assignment' => $employeeTrainingAssignment,
        ]);
    }

    public function complete(Request $request, EmployeeTrainingAssignment $employeeTrainingAssignment): RedirectResponse
    {
        $this->authorize('update', $employeeTrainingAssignment);

        $validated = $request->validate([
            'hours_completed' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'certificate_reference' => ['nullable', 'string', 'max:255'],
            'certificate_expires_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->assignments->complete($employeeTrainingAssignment, $validated, $request->user());

        return back()->with('status', __('Training marked as completed.'));
    }

    public function skillsMatrix(Request $request): View
    {
        $this->authorize('viewAny', EmployeeTrainingAssignment::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.training.skills-matrix', [
            'skills' => $this->assignments->skillsMatrix($companyId, $request->integer('employee_id') ?: null),
            'formData' => $this->assignments->formData($companyId),
            'filters' => $request->only(['employee_id']),
        ]);
    }
}
