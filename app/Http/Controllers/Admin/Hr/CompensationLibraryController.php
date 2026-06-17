<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\PayrollComponentCalculationType;
use App\Enums\PayrollComponentFrequency;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Controller;
use App\Models\Hr\CompensationAllowanceDefinition;
use App\Models\Hr\CompensationDeductionDefinition;
use App\Models\Hr\EmployeeCompensation;
use App\Support\Hr\CompensationComponentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompensationLibraryController extends Controller
{
    use HandlesModalFormResponses;

    public function __construct(
        protected CompensationComponentService $components,
    ) {}

    public function allowances(Request $request): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.allowances', [
            'definitions' => $this->components->paginateAllowanceLibrary($companyId),
        ]);
    }

    public function createAllowance(Request $request): View
    {
        $this->authorize('create', EmployeeCompensation::class);

        return view('admin.hr.compensation.allowances.create', [
            'calculationTypes' => PayrollComponentCalculationType::cases(),
            'frequencies' => PayrollComponentFrequency::cases(),
        ]);
    }

    public function storeAllowance(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateDefinition($request, $companyId, 'compensation_allowance_definitions');

        $this->components->storeAllowanceDefinition($companyId, $data);

        return $this->modalOrRedirect(
            __('Allowance added to library.'),
            redirect()->route('admin.hr.compensation.allowances'),
        );
    }

    public function updateAllowance(Request $request, CompensationAllowanceDefinition $allowanceDefinition): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);

        $data = $this->validateDefinition($request, $allowanceDefinition->company_id, 'compensation_allowance_definitions', $allowanceDefinition->id);
        $this->components->updateAllowanceDefinition($allowanceDefinition, $data);

        return back()->with('status', __('Allowance updated.'));
    }

    public function deductions(Request $request): View
    {
        $this->authorize('viewAny', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.compensation.deductions', [
            'definitions' => $this->components->paginateDeductionLibrary($companyId),
        ]);
    }

    public function createDeduction(Request $request): View
    {
        $this->authorize('create', EmployeeCompensation::class);

        return view('admin.hr.compensation.deductions.create', [
            'calculationTypes' => PayrollComponentCalculationType::cases(),
            'frequencies' => PayrollComponentFrequency::cases(),
        ]);
    }

    public function storeDeduction(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', EmployeeCompensation::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateDefinition($request, $companyId, 'compensation_deduction_definitions') + [
            'category' => $request->input('category', 'custom'),
        ];

        $this->components->storeDeductionDefinition($companyId, $data);

        return $this->modalOrRedirect(
            __('Deduction added to library.'),
            redirect()->route('admin.hr.compensation.deductions'),
        );
    }

    public function updateDeduction(Request $request, CompensationDeductionDefinition $deductionDefinition): RedirectResponse
    {
        $this->authorize('create', EmployeeCompensation::class);

        $data = $this->validateDefinition($request, $deductionDefinition->company_id, 'compensation_deduction_definitions', $deductionDefinition->id) + [
            'category' => $request->input('category', $deductionDefinition->category),
        ];
        $this->components->updateDeductionDefinition($deductionDefinition, $data);

        return back()->with('status', __('Deduction updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateDefinition(Request $request, int $companyId, string $table, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique($table, 'code')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'calculation_type' => ['required', Rule::enum(PayrollComponentCalculationType::class)],
            'frequency' => ['required', Rule::enum(PayrollComponentFrequency::class)],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'percentage_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);
    }
}
