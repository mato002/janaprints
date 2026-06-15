<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentStatus;
use App\Enums\Gender;
use App\Http\Controllers\Admin\Concerns\ExportsTabularIndex;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Services\EmailIdentity\CorporateEmailGeneratorService;
use App\Services\EmailIdentity\EmployeeActivationManagementService;
use App\Services\EmailIdentity\EmployeeActivationRoleResolver;
use App\Services\EmailIdentity\EmployeeOnboardingService;
use App\Support\Export\TabularExportWriter;
use App\Support\Organization\JobTitleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    use ExportsTabularIndex;
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', Employee::class);

        $employees = $this->scopeToTenant(
            Employee::query()->with(['company', 'branch', 'department', 'jobTitle', 'corporateMailbox', 'activations'])
        )->latest()->paginate(15);

        $activationManagement = app(EmployeeActivationManagementService::class);

        return view('admin.employees.index', compact('employees', 'activationManagement'));
    }

    public function export(Request $request, string $format, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', Employee::class);

        $employees = $this->scopeToTenant(
            Employee::query()->with(['branch'])
        )->latest()->get();

        $headers = [__('Employee'), __('Employee number'), __('Branch')];
        $rows = $employees->map(fn (Employee $employee) => [
            $employee->full_name,
            $employee->employee_number,
            $employee->branch?->name ?? '',
        ])->all();

        return $this->downloadTabularExport($writer, $format, 'employees', $headers, $rows, __('Employees'));
    }

    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('admin.employees.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $data = $this->validateEmployee($request);
        $personalEmail = $data['email'];
        $intendedRole = $data['activation_role'] ?? null;
        unset($data['activation_role']);

        $employee = Employee::query()->create($data);
        app(JobTitleService::class)->syncEmployeeDesignation($employee);
        app(EmployeeOnboardingService::class)->ensureOnboarded($employee, $personalEmail, $intendedRole);

        return redirect()->route('admin.employees.index')->with('status', __('Employee created. Onboarding invitation queued.'));
    }

    public function previewCorporateEmail(Request $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ]);

        $email = app(CorporateEmailGeneratorService::class)->preview(
            $validated['first_name'],
            $validated['last_name'],
        );

        return response()->json(['email' => $email]);
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

        $employee->load(['corporateMailbox', 'user.roles', 'activations']);
        $activationManagement = app(EmployeeActivationManagementService::class);
        $activationStatus = $activationManagement->activationDisplayStatus($employee);
        $latestActivation = $activationManagement->latestOpenActivation($employee)
            ?? $employee->activations()->latest('id')->first();
        $readinessChecks = app(\App\Services\EmailIdentity\EmailIdentityReadinessService::class)
            ->checks($employee->company_id);

        return view('admin.employees.edit', array_merge(
            [
                'employee' => $employee,
                'communicationTimeline' => $communicationTimeline,
                'emailTimeline' => $emailTimeline,
                'activationStatus' => $activationStatus,
                'latestActivation' => $latestActivation,
                'readinessChecks' => $readinessChecks,
            ],
            $this->formData($employee),
        ));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $employee->update($this->validateEmployee($request, $employee));
        app(JobTitleService::class)->syncEmployeeDesignation($employee->fresh());

        return redirect()->route('admin.employees.index')->with('status', __('Employee updated.'));
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('status', __('Employee deleted.'));
    }

    public function resendActivation(Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        app(EmployeeActivationManagementService::class)->resendInvitation($employee);

        return back()->with('status', __('Activation invitation resent.'));
    }

    public function regenerateActivation(Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        app(EmployeeActivationManagementService::class)->regenerateActivation($employee);

        return back()->with('status', __('Activation link regenerated and invitation queued.'));
    }

    protected function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $companyId = auth()->user()->hasRole('Super Admin')
            ? $request->input('company_id')
            : auth()->user()->company_id;

        $rules = [
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
            'email' => [$employee ? 'nullable' : 'required', 'email'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'kra_pin' => ['nullable', 'string', 'max:50'],
            'nhif_number' => ['nullable', 'string', 'max:50'],
            'nssf_number' => ['nullable', 'string', 'max:50'],
            'job_title_id' => [
                'nullable',
                Rule::exists('job_titles', 'id')->where('company_id', $companyId),
            ],
            'designation' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'employment_status' => ['required', Rule::enum(EmploymentStatus::class)],
            'photo' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];

        if ($this->canAssignActivationRole()) {
            $rules['activation_role'] = [
                'nullable',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value === 'Super Admin' && ! auth()->user()?->hasRole('Super Admin')) {
                        $fail(__('You cannot assign the Super Admin role.'));
                    }
                },
            ];
        }

        return $request->validate($rules);
    }

    protected function canAssignActivationRole(): bool
    {
        return auth()->user()?->can('roles.edit') ?? false;
    }

    protected function formData(?Employee $employee = null): array
    {
        $companyId = $employee?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;

        $companies = auth()->user()->hasRole('Super Admin')
            ? Company::query()->where('is_active', true)->orderBy('name')->get()
            : Company::query()->where('id', auth()->user()->company_id)->get();

        $roleResolver = app(EmployeeActivationRoleResolver::class);

        return [
            'companies' => $companies,
            'branches' => Branch::query()->where('company_id', $companyId)->get(),
            'departments' => Department::query()->where('company_id', $companyId)->get(),
            'jobTitles' => JobTitle::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('title')->get(),
            'genders' => Gender::cases(),
            'statuses' => EmploymentStatus::cases(),
            'mailDomain' => config('mailboxes.domain'),
            'assignableRoles' => $this->canAssignActivationRole()
                ? $roleResolver->assignableRolesFor()
                : collect(),
            'canAssignActivationRole' => $this->canAssignActivationRole(),
        ];
    }
}
