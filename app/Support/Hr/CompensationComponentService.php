<?php

namespace App\Support\Hr;

use App\Enums\PayrollComponentCalculationType;
use App\Enums\PayrollComponentFrequency;
use App\Models\Employee;
use App\Models\Hr\CompensationAllowanceDefinition;
use App\Models\Hr\CompensationDeductionDefinition;
use App\Models\Hr\PayrollAllowance;
use App\Models\Hr\PayrollDeduction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompensationComponentService
{
    public function paginateAllowanceLibrary(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return CompensationAllowanceDefinition::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function paginateDeductionLibrary(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return CompensationDeductionDefinition::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function storeAllowanceDefinition(int $companyId, array $data): CompensationAllowanceDefinition
    {
        return CompensationAllowanceDefinition::query()->create([
            'company_id' => $companyId,
            ...$this->definitionPayload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateAllowanceDefinition(CompensationAllowanceDefinition $definition, array $data): CompensationAllowanceDefinition
    {
        $definition->update($this->definitionPayload($data));

        return $definition->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function storeDeductionDefinition(int $companyId, array $data): CompensationDeductionDefinition
    {
        return CompensationDeductionDefinition::query()->create([
            'company_id' => $companyId,
            'category' => $data['category'] ?? 'custom',
            ...$this->definitionPayload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDeductionDefinition(CompensationDeductionDefinition $definition, array $data): CompensationDeductionDefinition
    {
        $definition->update([
            'category' => $data['category'] ?? $definition->category,
            ...$this->definitionPayload($data),
        ]);

        return $definition->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assignAllowance(Employee $employee, array $data): PayrollAllowance
    {
        $definition = isset($data['allowance_definition_id'])
            ? CompensationAllowanceDefinition::query()->find($data['allowance_definition_id'])
            : null;

        return PayrollAllowance::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'code' => $data['code'] ?? $definition?->code ?? 'CUSTOM',
            'name' => $data['name'] ?? $definition?->name ?? __('Custom Allowance'),
            'calculation_type' => $data['calculation_type'] ?? $definition?->calculation_type?->value ?? PayrollComponentCalculationType::Fixed->value,
            'frequency' => $data['frequency'] ?? $definition?->frequency?->value ?? PayrollComponentFrequency::Recurring->value,
            'amount' => $data['amount'] ?? $definition?->default_amount ?? 0,
            'percentage_rate' => $data['percentage_rate'] ?? $definition?->percentage_rate,
            'allowance_definition_id' => $definition?->id,
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assignDeduction(Employee $employee, array $data): PayrollDeduction
    {
        $definition = isset($data['deduction_definition_id'])
            ? CompensationDeductionDefinition::query()->find($data['deduction_definition_id'])
            : null;

        return PayrollDeduction::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'code' => $data['code'] ?? $definition?->code ?? 'CUSTOM',
            'name' => $data['name'] ?? $definition?->name ?? __('Custom Deduction'),
            'category' => $data['category'] ?? $definition?->category ?? 'custom',
            'calculation_type' => $data['calculation_type'] ?? $definition?->calculation_type?->value ?? PayrollComponentCalculationType::Fixed->value,
            'frequency' => $data['frequency'] ?? $definition?->frequency?->value ?? PayrollComponentFrequency::Recurring->value,
            'amount' => $data['amount'] ?? $definition?->default_amount ?? 0,
            'percentage_rate' => $data['percentage_rate'] ?? $definition?->percentage_rate,
            'deduction_definition_id' => $definition?->id,
            'is_active' => true,
        ]);
    }

    public function deactivateAllowance(PayrollAllowance $allowance): PayrollAllowance
    {
        $allowance->update(['is_active' => false]);

        return $allowance->fresh();
    }

    public function deactivateDeduction(PayrollDeduction $deduction): PayrollDeduction
    {
        $deduction->update(['is_active' => false]);

        return $deduction->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function definitionPayload(array $data): array
    {
        return [
            'code' => $data['code'],
            'name' => $data['name'],
            'calculation_type' => $data['calculation_type'] ?? PayrollComponentCalculationType::Fixed->value,
            'frequency' => $data['frequency'] ?? PayrollComponentFrequency::Recurring->value,
            'default_amount' => $data['default_amount'] ?? 0,
            'percentage_rate' => $data['percentage_rate'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];
    }
}
