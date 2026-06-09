<?php

namespace App\Support\Hr;

use App\Enums\CompensationStatus;
use App\Enums\PaymentFrequency;
use App\Enums\PayrollGroup;
use App\Models\Employee;
use App\Models\Hr\CompensationSalaryChange;
use App\Models\Hr\CompensationSalaryTemplate;
use App\Models\Hr\EmployeeCompensation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompensationService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateRegister(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['branch', 'department', 'compensation']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['payroll_group'])) {
            $query->whereHas('compensation', fn ($q) => $q->where('payroll_group', $filters['payroll_group']));
        }

        if (($filters['coverage'] ?? null) === 'missing') {
            $query->whereDoesntHave('compensation');
        }

        return $query->orderBy('employee_number')->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $activeEmployees = Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->count();

        $withCompensation = Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('compensation', fn ($q) => $q->where('status', CompensationStatus::Active))
            ->count();

        $pendingApproval = EmployeeCompensation::query()
            ->where('company_id', $companyId)
            ->where('status', CompensationStatus::PendingApproval)
            ->where('is_active', true)
            ->count();

        $avgGross = EmployeeCompensation::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('status', CompensationStatus::Active)
            ->get()
            ->avg(fn (EmployeeCompensation $c) => $c->grossComponents()) ?? 0;

        return [
            'active_employees' => $activeEmployees,
            'with_compensation' => $withCompensation,
            'missing_compensation' => max(0, $activeEmployees - $withCompensation),
            'pending_approval' => $pendingApproval,
            'avg_gross' => round((float) $avgGross, 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Employee $employee, array $data, User $user, bool $requireApproval = false): EmployeeCompensation
    {
        if ($employee->compensation) {
            throw ValidationException::withMessages([
                'employee_id' => __('Employee already has active compensation. Create a revision instead.'),
            ]);
        }

        return $this->storeNewRecord($employee, $data, $user, $requireApproval);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function revise(Employee $employee, array $data, User $user, bool $requireApproval = false): EmployeeCompensation
    {
        $current = $employee->compensation;

        return DB::transaction(function () use ($employee, $data, $user, $requireApproval, $current) {
            if ($current) {
                $current->update([
                    'is_active' => false,
                    'status' => CompensationStatus::Superseded,
                ]);
            }

            $record = $this->storeNewRecord($employee, $data, $user, $requireApproval);

            if ($current) {
                CompensationSalaryChange::query()->create([
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'employee_compensation_id' => $record->id,
                    'old_salary' => $current->basic_salary,
                    'new_salary' => $record->basic_salary,
                    'changed_by_user_id' => $user->id,
                    'reason' => $data['change_reason'] ?? null,
                    'effective_from' => $data['effective_from'],
                ]);
            }

            return $record;
        });
    }

    public function approve(EmployeeCompensation $compensation, User $user): EmployeeCompensation
    {
        if ($compensation->status !== CompensationStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'status' => __('Only pending compensation can be approved.'),
            ]);
        }

        $compensation->update([
            'status' => CompensationStatus::Active,
            'changed_by_user_id' => $user->id,
        ]);

        return $compensation->fresh();
    }

    public function applyTemplate(Employee $employee, CompensationSalaryTemplate $template, array $overrides, User $user): EmployeeCompensation
    {
        $data = [
            'basic_salary' => $overrides['basic_salary'] ?? $template->basic_salary,
            'house_allowance' => $overrides['house_allowance'] ?? $template->house_allowance,
            'transport_allowance' => $overrides['transport_allowance'] ?? $template->transport_allowance,
            'medical_allowance' => $overrides['medical_allowance'] ?? $template->medical_allowance,
            'risk_allowance' => $overrides['risk_allowance'] ?? $template->risk_allowance,
            'responsibility_allowance' => $overrides['responsibility_allowance'] ?? $template->responsibility_allowance,
            'payment_frequency' => $overrides['payment_frequency'] ?? $template->payment_frequency->value,
            'payroll_group' => $overrides['payroll_group'] ?? $template->payroll_group->value,
            'currency' => $overrides['currency'] ?? $template->currency,
            'effective_from' => $overrides['effective_from'],
            'change_reason' => $overrides['change_reason'] ?? __('Applied from template :name', ['name' => $template->name]),
            'salary_template_id' => $template->id,
        ];

        return $employee->compensation
            ? $this->revise($employee, $data, $user, $overrides['require_approval'] ?? false)
            : $this->create($employee, $data, $user, $overrides['require_approval'] ?? false);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, EmployeeCompensation>
     */
    public function historyForEmployee(Employee $employee)
    {
        return EmployeeCompensation::query()
            ->where('employee_id', $employee->id)
            ->with(['changedBy', 'salaryTemplate'])
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function storeNewRecord(Employee $employee, array $data, User $user, bool $requireApproval): EmployeeCompensation
    {
        $status = $requireApproval ? CompensationStatus::PendingApproval : CompensationStatus::Active;

        return EmployeeCompensation::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'basic_salary' => $data['basic_salary'],
            'house_allowance' => $data['house_allowance'] ?? 0,
            'transport_allowance' => $data['transport_allowance'] ?? 0,
            'medical_allowance' => $data['medical_allowance'] ?? 0,
            'risk_allowance' => $data['risk_allowance'] ?? 0,
            'responsibility_allowance' => $data['responsibility_allowance'] ?? 0,
            'effective_from' => $data['effective_from'],
            'payment_frequency' => $data['payment_frequency'] ?? PaymentFrequency::Monthly->value,
            'payroll_group' => $data['payroll_group'] ?? PayrollGroup::Main->value,
            'currency' => $data['currency'] ?? 'KES',
            'status' => $status,
            'change_reason' => $data['change_reason'] ?? null,
            'changed_by_user_id' => $user->id,
            'salary_template_id' => $data['salary_template_id'] ?? null,
            'is_active' => true,
        ]);
    }
}
