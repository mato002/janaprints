<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\ClearanceStatus;
use App\Enums\ExitType;
use App\Http\Controllers\Controller;
use App\Models\Hr\EmployeeExit;
use App\Models\Hr\EmployeeExitClearance;
use App\Support\Hr\EmployeeExitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeExitController extends Controller
{
    public function __construct(
        protected EmployeeExitService $exits,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeExit::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.exit.index', [
            'exits' => $this->exits->paginate($companyId, $request->only([
                'employee_id', 'exit_type', 'status',
            ])),
            'filters' => $request->only(['employee_id', 'exit_type', 'status']),
            'formData' => $this->exits->formData($companyId),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EmployeeExit::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.exit.create', [
            'formData' => $this->exits->formData($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmployeeExit::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $companyId)->where('is_active', true),
            ],
            'exit_type' => ['required', Rule::enum(ExitType::class)],
            'last_working_date' => ['required', 'date'],
            'exit_date' => ['nullable', 'date', 'after_or_equal:last_working_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $exit = $this->exits->initiate($companyId, $validated, $request->user());

        return redirect()
            ->route('admin.hr.exit.show', $exit)
            ->with('status', __('Exit process initiated.'));
    }

    public function show(EmployeeExit $employeeExit): View
    {
        $this->authorize('view', $employeeExit);

        $employeeExit->load(['employee', 'clearances.clearedBy', 'initiatedBy', 'settledBy', 'closedBy']);

        return view('admin.hr.exit.show', [
            'exit' => $employeeExit,
        ]);
    }

    public function updateClearance(Request $request, EmployeeExit $employeeExit, EmployeeExitClearance $clearance): RedirectResponse
    {
        $this->authorize('update', $employeeExit);
        abort_unless($clearance->employee_exit_id === $employeeExit->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ClearanceStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->exits->updateClearance(
            $clearance,
            ClearanceStatus::from($validated['status']),
            $request->user(),
            $validated['notes'] ?? null,
        );

        return back()->with('status', __('Clearance updated.'));
    }

    public function settle(Request $request, EmployeeExit $employeeExit): RedirectResponse
    {
        $this->authorize('update', $employeeExit);

        $this->exits->settle($employeeExit, $request->user());

        return back()->with('status', __('Final dues settled.'));
    }

    public function close(Request $request, EmployeeExit $employeeExit): RedirectResponse
    {
        $this->authorize('update', $employeeExit);

        $this->exits->close($employeeExit, $request->user());

        return redirect()
            ->route('admin.hr.exit.index')
            ->with('status', __('Exit process closed.'));
    }
}
