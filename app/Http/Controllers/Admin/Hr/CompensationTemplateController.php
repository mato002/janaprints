<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\PaymentFrequency;
use App\Enums\PayrollGroup;
use App\Http\Controllers\Controller;
use App\Models\Hr\CompensationSalaryTemplate;
use App\Models\Hr\EmployeeCompensation;
use App\Support\Hr\CompensationTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompensationTemplateController extends Controller
{
    public function __construct(
        protected CompensationTemplateService $templates,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.templates', [
            'templates' => $this->templates->paginate($companyId),
            'paymentFrequencies' => PaymentFrequency::cases(),
            'payrollGroups' => PayrollGroup::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateTemplate($request, $companyId);

        $this->templates->create($companyId, $data);

        return back()->with('status', __('Salary template created.'));
    }

    public function update(Request $request, CompensationSalaryTemplate $salaryTemplate): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);

        $data = $this->validateTemplate($request, $salaryTemplate->company_id, $salaryTemplate->id);
        $this->templates->update($salaryTemplate, $data);

        return back()->with('status', __('Salary template updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateTemplate(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('compensation_salary_templates', 'code')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'house_allowance' => ['nullable', 'numeric', 'min:0'],
            'transport_allowance' => ['nullable', 'numeric', 'min:0'],
            'medical_allowance' => ['nullable', 'numeric', 'min:0'],
            'risk_allowance' => ['nullable', 'numeric', 'min:0'],
            'responsibility_allowance' => ['nullable', 'numeric', 'min:0'],
            'payment_frequency' => ['required', Rule::enum(PaymentFrequency::class)],
            'payroll_group' => ['required', Rule::enum(PayrollGroup::class)],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['boolean'],
        ]);
    }
}
