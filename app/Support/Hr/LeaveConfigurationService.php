<?php

namespace App\Support\Hr;

use App\Enums\LeaveAccrualFrequency;
use App\Enums\LeaveUnit;
use App\Models\Branch;
use App\Models\Hr\LeaveAccrualRule;
use App\Models\Hr\LeaveCarryForwardRule;
use App\Models\Hr\LeavePolicy;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PublicHoliday;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class LeaveConfigurationService
{
    /**
     * @return array<string, int>
     */
    public function dashboardStats(int $companyId): array
    {
        return [
            'leave_types' => LeaveType::query()->where('company_id', $companyId)->where('is_active', true)->count(),
            'holidays' => PublicHoliday::query()->where('company_id', $companyId)->where('is_active', true)->count(),
            'policies' => LeavePolicy::query()->where('company_id', $companyId)->where('is_active', true)->count(),
            'accrual_rules' => LeaveAccrualRule::query()->where('company_id', $companyId)->where('is_active', true)->count(),
            'carry_rules' => LeaveCarryForwardRule::query()->where('company_id', $companyId)->where('is_active', true)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function centerData(int $companyId): array
    {
        return [
            'stats' => $this->dashboardStats($companyId),
            'leaveTypes' => $this->paginateLeaveTypes($companyId),
            'holidays' => $this->paginateHolidays($companyId),
            'policies' => $this->paginatePolicies($companyId),
            'accrualRules' => $this->paginateAccrualRules($companyId),
            'carryForwardRules' => $this->paginateCarryForwardRules($companyId),
            'leaveTypeOptions' => LeaveType::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'policyOptions' => LeavePolicy::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'units' => LeaveUnit::cases(),
            'frequencies' => LeaveAccrualFrequency::cases(),
        ];
    }

    public function paginateLeaveTypes(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return LeaveType::query()
            ->where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'types_page');
    }

    public function paginateHolidays(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return PublicHoliday::query()
            ->where('company_id', $companyId)
            ->with('branch')
            ->orderByDesc('holiday_date')
            ->paginate($perPage, ['*'], 'holidays_page');
    }

    public function paginatePolicies(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return LeavePolicy::query()
            ->where('company_id', $companyId)
            ->with('leaveType')
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'policies_page');
    }

    public function paginateAccrualRules(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return LeaveAccrualRule::query()
            ->where('company_id', $companyId)
            ->with(['leaveType', 'policy'])
            ->orderByDesc('effective_from')
            ->paginate($perPage, ['*'], 'accrual_page');
    }

    public function paginateCarryForwardRules(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return LeaveCarryForwardRule::query()
            ->where('company_id', $companyId)
            ->with(['leaveType', 'policy'])
            ->orderBy('leave_type_id')
            ->paginate($perPage, ['*'], 'carry_page');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createLeaveType(int $companyId, array $data): LeaveType
    {
        return LeaveType::query()->create([
            'company_id' => $companyId,
            ...$this->leaveTypePayload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateLeaveType(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($this->leaveTypePayload($data));

        return $leaveType->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createHoliday(int $companyId, array $data): PublicHoliday
    {
        return PublicHoliday::query()->create([
            'company_id' => $companyId,
            ...$this->holidayPayload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateHoliday(PublicHoliday $holiday, array $data): PublicHoliday
    {
        $holiday->update($this->holidayPayload($data));

        return $holiday->fresh(['branch']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPolicy(int $companyId, array $data): LeavePolicy
    {
        return LeavePolicy::query()->create([
            'company_id' => $companyId,
            ...$this->policyPayload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePolicy(LeavePolicy $policy, array $data): LeavePolicy
    {
        $policy->update($this->policyPayload($data));

        return $policy->fresh(['leaveType']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createAccrualRule(int $companyId, array $data): LeaveAccrualRule
    {
        return LeaveAccrualRule::query()->create([
            'company_id' => $companyId,
            ...$this->accrualPayload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAccrualRule(LeaveAccrualRule $rule, array $data): LeaveAccrualRule
    {
        $rule->update($this->accrualPayload($data));

        return $rule->fresh(['leaveType', 'policy']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCarryForwardRule(int $companyId, array $data): LeaveCarryForwardRule
    {
        return LeaveCarryForwardRule::query()->create([
            'company_id' => $companyId,
            ...$this->carryForwardPayload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCarryForwardRule(LeaveCarryForwardRule $rule, array $data): LeaveCarryForwardRule
    {
        $rule->update($this->carryForwardPayload($data));

        return $rule->fresh(['leaveType', 'policy']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function leaveTypePayload(array $data): array
    {
        return [
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'unit' => $data['unit'] ?? LeaveUnit::Days->value,
            'is_paid' => filter_var($data['is_paid'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'requires_supervisor_approval' => filter_var($data['requires_supervisor_approval'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'requires_hr_approval' => filter_var($data['requires_hr_approval'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'default_days_per_year' => $data['default_days_per_year'] ?? null,
            'accrual_days_per_month' => $data['accrual_days_per_month'] ?? null,
            'allow_half_day' => filter_var($data['allow_half_day'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => $data['sort_order'] ?? 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function holidayPayload(array $data): array
    {
        return [
            'branch_id' => $data['branch_id'] ?? null,
            'name' => $data['name'],
            'region' => $data['region'] ?? null,
            'holiday_date' => $data['holiday_date'],
            'is_recurring' => filter_var($data['is_recurring'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function policyPayload(array $data): array
    {
        return [
            'leave_type_id' => $data['leave_type_id'],
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'min_notice_days' => $data['min_notice_days'] ?? 0,
            'max_consecutive_days' => $data['max_consecutive_days'] ?? null,
            'requires_documentation' => $data['requires_documentation'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function accrualPayload(array $data): array
    {
        return [
            'leave_type_id' => $data['leave_type_id'],
            'leave_policy_id' => $data['leave_policy_id'] ?? null,
            'frequency' => $data['frequency'] ?? LeaveAccrualFrequency::Monthly->value,
            'rate_per_period' => $data['rate_per_period'] ?? 0,
            'custom_interval_days' => $data['custom_interval_days'] ?? null,
            'effective_from' => $data['effective_from'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function carryForwardPayload(array $data): array
    {
        return [
            'leave_type_id' => $data['leave_type_id'],
            'leave_policy_id' => $data['leave_policy_id'] ?? null,
            'max_carry_days' => $data['max_carry_days'] ?? 0,
            'expiry_month' => $data['expiry_month'] ?? null,
            'expiry_day' => $data['expiry_day'] ?? null,
            'policy_notes' => $data['policy_notes'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];
    }
}
