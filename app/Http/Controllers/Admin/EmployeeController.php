<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', Employee::class);

        $employees = $this->scopeToTenant(
            Employee::query()->with(['company', 'branch', 'department'])
        )->latest()->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('admin.employees.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        Employee::query()->create($this->validateEmployee($request));

        return redirect()->route('admin.employees.index')->with('status', __('Employee created.'));
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        $logService = app(\App\Support\Communications\CommunicationLogService::class);
        $communicationTimeline = auth()->user()->can('communications.logs.view')
            ? $logService->forEntity('employee', $employee->id, $employee->company_id)
            : collect();
        $emailTimeline = auth()->user()->can('communications.logs.view')
            ? $logService->forEntity('employee', $employee->id, $employee->company_id, 15, \App\Enums\CommunicationLogChannel::Email)
            : collect();

        return view('admin.employees.edit', array_merge(
            ['employee' => $employee, 'communicationTimeline' => $communicationTimeline, 'emailTimeline' => $emailTimeline],
            $this->formData($employee),
        ));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $employee->update($this->validateEmployee($request, $employee));

        return redirect()->route('admin.employees.index')->with('status', __('Employee updated.'));
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('status', __('Employee deleted.'));
    }

    protected function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $companyId = auth()->user()->hasRole('Super Admin')
            ? $request->input('company_id')
            : auth()->user()->company_id;

        return $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')->where('company_id', $companyId),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where('company_id', $companyId),
            ],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')
                    ->where('company_id', $companyId)
                    ->ignore($employee?->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'kra_pin' => ['nullable', 'string', 'max:50'],
            'nhif_number' => ['nullable', 'string', 'max:50'],
            'nssf_number' => ['nullable', 'string', 'max:50'],
            'designation' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
            'photo' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
    }

    protected function formData(?Employee $employee = null): array
    {
        $companyId = $employee?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;

        $companies = auth()->user()->hasRole('Super Admin')
            ? Company::query()->where('is_active', true)->orderBy('name')->get()
            : Company::query()->where('id', auth()->user()->company_id)->get();

        return [
            'companies' => $companies,
            'branches' => Branch::query()->where('company_id', $companyId)->get(),
            'departments' => Department::query()->where('company_id', $companyId)->get(),
            'genders' => Gender::cases(),
            'statuses' => EmploymentStatus::cases(),
        ];
    }
}
