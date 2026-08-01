<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\PaymentFrequency;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ResolvesEntityCode;
use App\Http\Controllers\Controller;
use App\Models\Hr\CompensationSalaryTemplate;
use App\Models\Hr\EmployeeCompensation;
use App\Support\Hr\CompensationTemplateService;
use App\Support\Hr\PayrollGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompensationTemplateController extends Controller
{
    use HandlesModalFormResponses;
    use ResolvesEntityCode;

    public function __construct(
        protected CompensationTemplateService $templates,
        protected PayrollGroupService $payrollGroups,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.templates', [
            'templates' => $this->templates->paginate($companyId),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EmployeeCompensation::class);

        return view('admin.hr.compensation.templates.create', [
            'paymentFrequencies' => PaymentFrequency::cases(),
            'payrollGroups' => $this->payrollGroups->activeForCompany(tenant()->companyId() ?? $request->user()->company_id),
        ]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateTemplate($request, $companyId);
        $data['is_active'] = true;

        $this->templates->create($companyId, $data);

        return $this->modalOrRedirect(
            __('Payroll class created.'),
            redirect()->route('admin.hr.compensation.templates'),
        );
    }

    public function edit(CompensationSalaryTemplate $salaryTemplate): View
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->ensureTemplateAccess($salaryTemplate);

        return view('admin.hr.compensation.templates.edit', [
            'template' => $salaryTemplate,
            'usageCount' => $this->templates->usageCount($salaryTemplate),
            'paymentFrequencies' => PaymentFrequency::cases(),
            'payrollGroups' => $this->payrollGroups->activeForCompany(tenant()->companyId() ?? $request->user()->company_id),
        ]);
    }

    public function update(Request $request, CompensationSalaryTemplate $salaryTemplate): RedirectResponse|Response
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->ensureTemplateAccess($salaryTemplate);

        $data = $this->validateTemplate($request, $salaryTemplate->company_id, $salaryTemplate->id);
        $data['is_active'] = $request->boolean('is_active');

        $this->templates->update($salaryTemplate, $data);

        return $this->modalOrRedirect(
            __('Payroll class updated.'),
            redirect()->route('admin.hr.compensation.templates'),
        );
    }

    public function deactivate(CompensationSalaryTemplate $salaryTemplate): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->ensureTemplateAccess($salaryTemplate);

        $this->templates->deactivate($salaryTemplate);

        return back()->with('status', __('Payroll class deactivated.'));
    }

    public function reactivate(CompensationSalaryTemplate $salaryTemplate): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->ensureTemplateAccess($salaryTemplate);

        $this->templates->reactivate($salaryTemplate);

        return back()->with('status', __('Payroll class reactivated.'));
    }

    public function destroy(CompensationSalaryTemplate $salaryTemplate): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->ensureTemplateAccess($salaryTemplate);

        $this->templates->delete($salaryTemplate);

        return redirect()
            ->route('admin.hr.compensation.templates')
            ->with('status', __('Payroll class deleted.'));
    }

    protected function ensureTemplateAccess(CompensationSalaryTemplate $salaryTemplate): void
    {
        $companyId = tenant()->companyId() ?? auth()->user()?->company_id;

        abort_unless($salaryTemplate->company_id === $companyId, 404);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateTemplate(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        $this->payrollGroups->ensureDefaults($companyId);

        $validated = $request->validate([
            'code' => array_merge(
                $this->nullableCodeRules(30),
                [Rule::unique('compensation_salary_templates', 'code')->where('company_id', $companyId)->ignore($ignoreId)],
            ),
            'name' => ['required', 'string', 'max:255'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'house_allowance' => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'medical_allowance' => ['nullable', 'numeric', 'min:0'],
            'risk_allowance' => ['nullable', 'numeric', 'min:0'],
            'responsibility_allowance' => ['nullable', 'numeric', 'min:0'],
            'payment_frequency' => ['required', Rule::enum(PaymentFrequency::class)],
            'payroll_group' => [
                'required',
                'string',
                'max:30',
                Rule::exists('payroll_group_definitions', 'code')
                    ->where('company_id', $companyId)
                    ->where('is_active', true),
            ],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $validated['code'] = $this->resolveCompanyScopedCode(
            $request,
            'name',
            CompensationSalaryTemplate::class,
            $companyId,
            $ignoreId,
            30,
        );

        return $validated;
    }
}
