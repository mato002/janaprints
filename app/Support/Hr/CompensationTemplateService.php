<?php

namespace App\Support\Hr;

use App\Models\Hr\CompensationSalaryTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompensationTemplateService
{
    public function paginate(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return CompensationSalaryTemplate::query()
            ->where('company_id', $companyId)
            ->withCount('employeeCompensations as usage_count')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): CompensationSalaryTemplate
    {
        return CompensationSalaryTemplate::query()->create([
            'company_id' => $companyId,
            ...$this->payload($data),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CompensationSalaryTemplate $template, array $data): CompensationSalaryTemplate
    {
        $template->update($this->payload($data));

        return $template->fresh();
    }

    public function deactivate(CompensationSalaryTemplate $template): CompensationSalaryTemplate
    {
        $template->update(['is_active' => false]);

        return $template->fresh();
    }

    public function reactivate(CompensationSalaryTemplate $template): CompensationSalaryTemplate
    {
        $template->update(['is_active' => true]);

        return $template->fresh();
    }

    public function delete(CompensationSalaryTemplate $template): void
    {
        if ($template->employeeCompensations()->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'template' => __('This payroll class is assigned to employees and cannot be deleted. Deactivate it instead.'),
            ]);
        }

        $template->delete();
    }

    public function usageCount(CompensationSalaryTemplate $template): int
    {
        return $template->employeeCompensations()->count();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function payload(array $data): array
    {
        return [
            'code' => $data['code'],
            'name' => $data['name'],
            'basic_salary' => $data['basic_salary'] ?? 0,
            'house_allowance' => $data['house_allowance'] ?? 0,
            'transport_allowance' => $data['transport_allowance'] ?? 0,
            'medical_allowance' => $data['medical_allowance'] ?? 0,
            'risk_allowance' => $data['risk_allowance'] ?? 0,
            'responsibility_allowance' => $data['responsibility_allowance'] ?? 0,
            'payment_frequency' => $data['payment_frequency'] ?? 'monthly',
            'payroll_group' => $data['payroll_group'] ?? 'main',
            'currency' => $data['currency'] ?? 'KES',
            'is_active' => $data['is_active'] ?? true,
        ];
    }
}
