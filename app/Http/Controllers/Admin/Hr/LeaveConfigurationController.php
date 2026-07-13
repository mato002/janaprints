<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\LeaveAccrualFrequency;
use App\Enums\LeaveUnit;
use App\Http\Controllers\Controller;
use App\Models\Hr\LeaveAccrualRule;
use App\Models\Hr\LeaveCarryForwardRule;
use App\Models\Hr\LeavePolicy;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PublicHoliday;
use App\Support\Hr\LeaveConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveConfigurationController extends Controller
{
    public function __construct(
        protected LeaveConfigurationService $config,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveType::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.leave.config.index', $this->config->centerData($companyId));
    }

    public function storeLeaveType(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveType::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateLeaveType($request, $companyId);
        $this->config->createLeaveType($companyId, $data);

        return $this->redirectToSetup('leave-types', __('Leave type created.'));
    }

    public function updateLeaveType(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $this->authorize('update', LeaveType::class);

        $data = $this->validateLeaveType($request, $leaveType->company_id, $leaveType->id);
        $this->config->updateLeaveType($leaveType, $data);

        return $this->redirectToSetup('leave-types', __('Leave type updated.'));
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveType::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateHoliday($request, $companyId);
        $this->config->createHoliday($companyId, $data);

        return $this->redirectToSetup('holidays', __('Public holiday created.'));
    }

    public function updateHoliday(Request $request, PublicHoliday $publicHoliday): RedirectResponse
    {
        $this->authorize('update', LeaveType::class);

        $data = $this->validateHoliday($request, $publicHoliday->company_id);
        $this->config->updateHoliday($publicHoliday, $data);

        return $this->redirectToSetup('holidays', __('Public holiday updated.'));
    }

    public function storePolicy(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveType::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validatePolicy($request, $companyId);
        $this->config->createPolicy($companyId, $data);

        return $this->redirectToSetup('policies', __('Leave policy created.'));
    }

    public function updatePolicy(Request $request, LeavePolicy $leavePolicy): RedirectResponse
    {
        $this->authorize('update', LeaveType::class);

        $data = $this->validatePolicy($request, $leavePolicy->company_id, $leavePolicy->id);
        $this->config->updatePolicy($leavePolicy, $data);

        return $this->redirectToSetup('policies', __('Leave policy updated.'));
    }

    public function storeAccrualRule(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveType::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateAccrualRule($request, $companyId);
        $this->config->createAccrualRule($companyId, $data);

        return $this->redirectToSetup('accrual-rules', __('Accrual rule created.'));
    }

    public function updateAccrualRule(Request $request, LeaveAccrualRule $leaveAccrualRule): RedirectResponse
    {
        $this->authorize('update', LeaveType::class);

        $data = $this->validateAccrualRule($request, $leaveAccrualRule->company_id);
        $this->config->updateAccrualRule($leaveAccrualRule, $data);

        return $this->redirectToSetup('accrual-rules', __('Accrual rule updated.'));
    }

    public function storeCarryForwardRule(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveType::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;
        $data = $this->validateCarryForwardRule($request, $companyId);
        $this->config->createCarryForwardRule($companyId, $data);

        return $this->redirectToSetup('carry-forward', __('Carry forward rule created.'));
    }

    public function updateCarryForwardRule(Request $request, LeaveCarryForwardRule $leaveCarryForwardRule): RedirectResponse
    {
        $this->authorize('update', LeaveType::class);

        $data = $this->validateCarryForwardRule($request, $leaveCarryForwardRule->company_id);
        $this->config->updateCarryForwardRule($leaveCarryForwardRule, $data);

        return $this->redirectToSetup('carry-forward', __('Carry forward rule updated.'));
    }

    protected function redirectToSetup(string $section, string $status): RedirectResponse
    {
        $params = array_filter([
            'tab' => 'setup',
            'setup' => $section,
            'embedded' => request('embedded'),
        ]);

        return redirect()
            ->route('admin.hr.leave.dashboard', $params)
            ->with('status', $status);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateLeaveType(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => [
                'required', 'string', 'max:30',
                Rule::unique('leave_types', 'code')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', Rule::enum(LeaveUnit::class)],
            'is_paid' => ['boolean'],
            'requires_supervisor_approval' => ['boolean'],
            'requires_hr_approval' => ['boolean'],
            'default_days_per_year' => ['nullable', 'numeric', 'min:0'],
            'accrual_days_per_month' => ['nullable', 'numeric', 'min:0'],
            'allow_half_day' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHoliday(Request $request, int $companyId): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date'],
            'region' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'is_recurring' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePolicy(Request $request, int $companyId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('company_id', $companyId)],
            'code' => [
                'required', 'string', 'max:30',
                Rule::unique('leave_policies', 'code')->where('company_id', $companyId)->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_notice_days' => ['nullable', 'integer', 'min:0'],
            'max_consecutive_days' => ['nullable', 'numeric', 'min:0'],
            'requires_documentation' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateAccrualRule(Request $request, int $companyId): array
    {
        return $request->validate([
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('company_id', $companyId)],
            'leave_policy_id' => ['nullable', Rule::exists('leave_policies', 'id')->where('company_id', $companyId)],
            'frequency' => ['required', Rule::enum(LeaveAccrualFrequency::class)],
            'rate_per_period' => ['required', 'numeric', 'min:0'],
            'custom_interval_days' => ['nullable', 'integer', 'min:1'],
            'effective_from' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateCarryForwardRule(Request $request, int $companyId): array
    {
        return $request->validate([
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->where('company_id', $companyId)],
            'leave_policy_id' => ['nullable', Rule::exists('leave_policies', 'id')->where('company_id', $companyId)],
            'max_carry_days' => ['required', 'numeric', 'min:0'],
            'expiry_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'expiry_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'policy_notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);
    }
}
