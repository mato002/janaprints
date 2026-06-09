<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\PayrollComponentCalculationType;
use App\Enums\PayrollComponentFrequency;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Hr\CompensationAllowanceDefinition;
use App\Models\Hr\CompensationDeductionDefinition;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\PayrollAllowance;
use App\Models\Hr\PayrollDeduction;
use App\Support\Hr\CompensationComponentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeCompensationComponentController extends Controller
{
    public function __construct(
        protected CompensationComponentService $components,
    ) {}

    public function storeAllowance(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->authorize('update', $employee);

        $data = $this->validateAllowance($request, $employee->company_id);
        $this->components->assignAllowance($employee, $data);

        return back()->with('status', __('Allowance assigned.'));
    }

    public function destroyAllowance(Employee $employee, PayrollAllowance $allowance): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        abort_unless($allowance->employee_id === $employee->id, 404);

        $this->components->deactivateAllowance($allowance);

        return back()->with('status', __('Allowance removed.'));
    }

    public function storeDeduction(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        $this->authorize('update', $employee);

        $data = $this->validateDeduction($request, $employee->company_id);
        $this->components->assignDeduction($employee, $data);

        return back()->with('status', __('Deduction assigned.'));
    }

    public function destroyDeduction(Employee $employee, PayrollDeduction $deduction): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);
        abort_unless($deduction->employee_id === $employee->id, 404);

        $this->components->deactivateDeduction($deduction);

        return back()->with('status', __('Deduction removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateAllowance(Request $request, int $companyId): array
    {
        return $request->validate([
            'allowance_definition_id' => [
                'nullable',
                Rule::exists('compensation_allowance_definitions', 'id')->where('company_id', $companyId),
            ],
            'code' => ['nullable', 'string', 'max:30'],
            'name' => ['nullable', 'string', 'max:255'],
            'calculation_type' => ['nullable', Rule::enum(PayrollComponentCalculationType::class)],
            'frequency' => ['nullable', Rule::enum(PayrollComponentFrequency::class)],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateDeduction(Request $request, int $companyId): array
    {
        return $request->validate([
            'deduction_definition_id' => [
                'nullable',
                Rule::exists('compensation_deduction_definitions', 'id')->where('company_id', $companyId),
            ],
            'code' => ['nullable', 'string', 'max:30'],
            'name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:30'],
            'calculation_type' => ['nullable', Rule::enum(PayrollComponentCalculationType::class)],
            'frequency' => ['nullable', Rule::enum(PayrollComponentFrequency::class)],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
