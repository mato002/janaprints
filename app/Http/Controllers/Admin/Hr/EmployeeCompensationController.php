<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\CompensationStatus;
use App\Enums\PaymentFrequency;
use App\Enums\PayrollGroup;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Hr\CompensationSalaryTemplate;
use App\Models\Hr\EmployeeCompensation;
use App\Support\Hr\CompensationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeCompensationController extends Controller
{
    public function __construct(
        protected CompensationService $compensation,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $filters = $request->only(['branch_id', 'payroll_group', 'coverage']);

        return view('admin.hr.compensation.register', [
            'employees' => $this->compensation->paginateRegister($companyId, $filters),
            'filters' => $filters,
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'payrollGroups' => PayrollGroup::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.form', [
            'employee' => null,
            'compensation' => null,
            'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('employee_number')->get(),
            'templates' => CompensationSalaryTemplate::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'paymentFrequencies' => PaymentFrequency::cases(),
            'payrollGroups' => PayrollGroup::cases(),
            'action' => route('admin.hr.compensation.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);

        $data = $this->validateCompensation($request);
        $employee = Employee::query()->findOrFail($data['employee_id']);

        $this->compensation->create($employee, $data, $request->user(), $request->boolean('require_approval'));

        return redirect()
            ->route('admin.hr.compensation.register')
            ->with('status', __('Compensation created.'));
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $employee->load('compensation');

        return view('admin.hr.compensation.form', [
            'employee' => $employee,
            'compensation' => $employee->compensation,
            'employees' => collect([$employee]),
            'templates' => CompensationSalaryTemplate::query()->where('company_id', $employee->company_id)->where('is_active', true)->orderBy('name')->get(),
            'paymentFrequencies' => PaymentFrequency::cases(),
            'payrollGroups' => PayrollGroup::cases(),
            'action' => route('admin.hr.compensation.update', $employee),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);

        $data = $this->validateCompensation($request, $employee);
        $this->compensation->revise($employee, $data, $request->user(), $request->boolean('require_approval'));

        return redirect()
            ->route('admin.hr.employees.show', ['employee' => $employee, 'tab' => 'compensation'])
            ->with('status', __('Compensation updated.'));
    }

    public function approve(EmployeeCompensation $compensation): RedirectResponse
    {
        $this->authorize('approve', $compensation);

        $this->compensation->approve($compensation, request()->user());

        return back()->with('status', __('Compensation approved.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateCompensation(Request $request, ?Employee $employee = null): array
    {
        $companyId = $employee?->company_id ?? tenant()->companyId() ?? $request->user()->company_id;

        return $request->validate([
            'employee_id' => [
                $employee ? 'nullable' : 'required',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'house_allowance' => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'medical_allowance' => ['nullable', 'numeric', 'min:0'],
            'risk_allowance' => ['nullable', 'numeric', 'min:0'],
            'responsibility_allowance' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'payment_frequency' => ['required', Rule::enum(PaymentFrequency::class)],
            'payroll_group' => ['required', Rule::enum(PayrollGroup::class)],
            'currency' => ['required', 'string', 'size:3'],
            'change_reason' => ['nullable', 'string', 'max:1000'],
            'salary_template_id' => [
                'nullable',
                Rule::exists('compensation_salary_templates', 'id')->where('company_id', $companyId),
            ],
            'require_approval' => ['boolean'],
        ]) + ($employee ? ['employee_id' => $employee->id] : []);
    }
}
