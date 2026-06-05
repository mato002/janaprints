<?php

namespace App\Support\Hr;

use App\Models\Company;
use App\Models\Hr\LeaveType;
use Illuminate\Database\Eloquent\Collection;

class LeaveTypeService
{
    /**
     * @return Collection<int, LeaveType>
     */
    public function forCompany(int $companyId, bool $activeOnly = true): Collection
    {
        $query = LeaveType::query()
            ->where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function seedDefaultsForCompany(Company $company): void
    {
        $defaults = [
            ['code' => 'ANNUAL', 'name' => 'Annual Leave', 'default_days_per_year' => 21, 'accrual_days_per_month' => 1.75, 'sort_order' => 10],
            ['code' => 'SICK', 'name' => 'Sick Leave', 'default_days_per_year' => 14, 'sort_order' => 20],
            ['code' => 'COMPASSIONATE', 'name' => 'Compassionate Leave', 'default_days_per_year' => 5, 'sort_order' => 30],
            ['code' => 'MATERNITY', 'name' => 'Maternity Leave', 'default_days_per_year' => 90, 'requires_supervisor_approval' => false, 'sort_order' => 40],
            ['code' => 'PATERNITY', 'name' => 'Paternity Leave', 'default_days_per_year' => 14, 'sort_order' => 50],
            ['code' => 'STUDY', 'name' => 'Study Leave', 'default_days_per_year' => 10, 'sort_order' => 60],
            ['code' => 'UNPAID', 'name' => 'Unpaid Leave', 'is_paid' => false, 'default_days_per_year' => null, 'sort_order' => 70],
        ];

        foreach ($defaults as $type) {
            LeaveType::query()->firstOrCreate(
                ['company_id' => $company->id, 'code' => $type['code']],
                array_merge([
                    'is_paid' => true,
                    'requires_supervisor_approval' => true,
                    'requires_hr_approval' => true,
                    'allow_half_day' => true,
                    'is_active' => true,
                ], $type),
            );
        }
    }
}
