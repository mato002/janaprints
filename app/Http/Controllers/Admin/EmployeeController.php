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
use App\Models\Hr\CompensationSalaryTemplate;
use App\Models\Hr\EmployeeCompensation;
use App\Models\JobTitle;
use App\Services\EmailIdentity\EmployeeActivationManagementService;
use App\Services\EmailIdentity\EmployeeActivationRoleResolver;
use App\Services\EmailIdentity\EmployeeOnboardingService;
use App\Support\Export\TabularExportWriter;
use App\Support\Hr\CompensationService;
use App\Support\Hr\EmployeeAccessGovernanceService;
use App\Support\Hr\EmployeeLifecycleService;
use App\Support\Hr\EmployeeNumberService;
use App\Support\Hr\EmployeeRosterQuery;
use App\Support\Organization\JobTitleService;
use App\Support\Platform\FormSettingsService;
use App\Models\User;
use App\Rules\MinimumAge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    use ExportsTabularIndex;
    use ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $companyId = EmployeeRosterQuery::resolveCompanyId($request->user());
        $filters = EmployeeRosterQuery::filtersFromRequest($request);

        $employees = EmployeeRosterQuery::paginate(
            $filters,
            15,
            ['company', 'branch', 'department', 'jobTitle', 'activations', 'user.roles', 'compensation'],
        );

        $activationManagement = app(EmployeeActivationManagementService::class);

        $branches = $companyId
            ? Branch::query()->where('company_id', $companyId)->orderBy('name')->get()
            : collect();

        return view('admin.employees.index', compact('employees', 'activationManagement', 'filters', 'branches'));
    }

    public function export(Request $request, string $format, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', Employee::class);

        $filters = EmployeeRosterQuery::filtersFromRequest($request);

        $employees = EmployeeRosterQuery::query(null, $filters)
            ->with(['branch'])
            ->orderBy('employee_number')
            ->get();

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
        $data['employee_number'] = app(EmployeeNumberService::class)->nextForCompany((int) $data['company_id']);

        $personalEmail = $data['email'];
        $intendedRole = $data['activation_role'] ?? null;
        $salaryTemplateId = $data['salary_template_id'] ?? null;
        unset($data['activation_role'], $data['salary_template_id']);

        $employee = DB::transaction(function () use ($data, $request, $personalEmail, $intendedRole, $salaryTemplateId) {
            $employee = Employee::query()->create($data);
            app(JobTitleService::class)->syncEmployeeDesignation($employee);
            app(EmployeeOnboardingService::class)->ensureOnboarded($employee, $personalEmail, $intendedRole);

            if ($salaryTemplateId && auth()->user()->can('create', EmployeeCompensation::class)) {
                $template = CompensationSalaryTemplate::query()
                    ->where('company_id', $employee->company_id)
                    ->where('is_active', true)
                    ->find($salaryTemplateId);

                if ($template) {
                    app(CompensationService::class)->applyTemplate($employee, $template, [
                        'effective_from' => $employee->hire_date?->toDateString() ?? now()->toDateString(),
                        'change_reason' => __('Initial assignment from payroll class :name', ['name' => $template->name]),
                    ], $request->user());
                }
            }

            return $employee->fresh(['compensation']);
        });

        $status = __('Employee created. Onboarding invitation queued.');

        if ($salaryTemplateId && $employee->compensation) {
            $template = CompensationSalaryTemplate::query()->find($salaryTemplateId);

            if ($template) {
                $status = __('Employee created with :class pay package. Onboarding invitation queued.', [
                    'class' => $template->name,
                ]);
            }
        }

        return redirect()->route('admin.employees.index')->with('status', $status);
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('admin.employees.edit', array_merge(
            ['employee' => $employee],
            $this->formData($employee),
        ));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $previousStatus = $employee->employment_status;
        $employee->update($this->validateEmployee($request, $employee));
        $employee->refresh();
        app(JobTitleService::class)->syncEmployeeDesignation($employee);

        $governance = app(EmployeeAccessGovernanceService::class);
        $actor = $request->user();

        if ($previousStatus !== EmploymentStatus::Suspended && $employee->employment_status === EmploymentStatus::Suspended) {
            $governance->onSuspended($employee, $actor);
        } elseif ($previousStatus === EmploymentStatus::Suspended && $employee->employment_status === EmploymentStatus::Active) {
            $governance->onReactivated($employee, $actor);
        }

        return redirect()->route('admin.employees.index')->with('status', __('Employee updated.'));
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        app(EmployeeLifecycleService::class)->purge($employee);

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
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'date_of_birth' => ['nullable', 'date', new MinimumAge(18, 100, $employee?->date_of_birth)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                $employee ? 'nullable' : 'required',
                'email',
                Rule::unique('users', 'email')->ignore($employee?->user?->id),
            ],
            'address' => ['nullable', 'string', 'max:2000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:50'],
            'next_of_kin_relationship' => ['nullable', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'kra_pin' => ['nullable', 'string', 'max:50'],
            'nhif_number' => ['nullable', 'string', 'max:50'],
            'nssf_number' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_branch_code' => ['nullable', 'string', 'max:50'],
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

        if (! $employee && auth()->user()?->can('create', EmployeeCompensation::class)) {
            $rules['salary_template_id'] = [
                'nullable',
                Rule::exists('compensation_salary_templates', 'id')
                    ->where('company_id', $companyId)
                    ->where('is_active', true),
            ];
        }

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

        $branchId = tenant()->branchId() ?? auth()->user()?->default_branch_id;

        return $this->formSettings->validateRequest(
            $request,
            'employee.create',
            $rules,
            (int) $companyId,
            $branchId ? (int) $branchId : null,
        );
    }

    protected function canAssignActivationRole(): bool
    {
        return auth()->user()?->can('roles.edit') ?? false;
    }

    protected function formData(?Employee $employee = null): array
    {
        $companyId = $employee?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = tenant()->branchId() ?? auth()->user()?->default_branch_id;

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
            'assignableRoles' => $this->canAssignActivationRole()
                ? $roleResolver->assignableRolesFor()
                : collect(),
            'canAssignActivationRole' => $this->canAssignActivationRole(),
            'payrollClasses' => auth()->user()?->can('create', EmployeeCompensation::class)
                ? CompensationSalaryTemplate::query()
                    ->where('company_id', $companyId)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get()
                : collect(),
            'suggestedEmployeeNumber' => $employee?->employee_number
                ?? app(EmployeeNumberService::class)->nextForCompany($companyId),
            'employeeNumberPrefix' => app(EmployeeNumberService::class)->prefixForCompany($companyId),
            'formFields' => $this->formSettings->resolvedFields('employee.create', $companyId, $branchId),
        ];
    }
}
