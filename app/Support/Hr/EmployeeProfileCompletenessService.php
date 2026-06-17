<?php

namespace App\Support\Hr;

use App\Models\Employee;

class EmployeeProfileCompletenessService
{
    /**
     * @return list<array{key: string, label: string}>
     */
    public function missingForPayroll(Employee $employee): array
    {
        $checks = [
            'kra_pin' => __('KRA PIN'),
            'nssf_number' => __('NSSF number'),
            'nhif_number' => __('SHIF/NHIF number'),
            'bank_name' => __('Bank name'),
            'bank_account_number' => __('Bank account number'),
        ];

        $missing = [];

        foreach ($checks as $field => $label) {
            if (blank($employee->{$field})) {
                $missing[] = ['key' => $field, 'label' => $label];
            }
        }

        return $missing;
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function missingRecommended(Employee $employee): array
    {
        $checks = [
            'national_id' => __('National ID / passport'),
            'phone' => __('Phone number'),
            'address' => __('Residential address'),
            'emergency_contact_name' => __('Emergency contact'),
            'emergency_contact_phone' => __('Emergency contact phone'),
        ];

        $missing = [];

        foreach ($checks as $field => $label) {
            if (blank($employee->{$field})) {
                $missing[] = ['key' => $field, 'label' => $label];
            }
        }

        return $missing;
    }

    public function isPayrollReady(Employee $employee): bool
    {
        return $this->missingForPayroll($employee) === [];
    }
}
