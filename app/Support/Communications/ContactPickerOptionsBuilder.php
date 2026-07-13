<?php

namespace App\Support\Communications;

use App\Models\Branch;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Procurement\Vendor;
use App\Models\Sales\CustomerInvoice;
use BackedEnum;

class ContactPickerOptionsBuilder
{
    /**
     * @return array{
     *     branches: \Illuminate\Support\Collection<int, Branch>,
     *     departments: \Illuminate\Support\Collection<int, Department>,
     *     pickerOptions: array<string, list<array<string, mixed>>>
     * }
     */
    public function forCompany(int $companyId): array
    {
        $withPhone = fn (?string $phone): bool => strlen(preg_replace('/\D+/', '', (string) $phone) ?? '') >= 9;

        return [
            'branches' => Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'departments' => Department::query()
                ->where('company_id', $companyId)
                ->orderBy('name')
                ->get(),
            'pickerOptions' => [
                'customers' => $this->customers($companyId, $withPhone),
                'leads' => $this->leads($companyId, $withPhone),
                'employees' => $this->employees($companyId, $withPhone),
                'suppliers' => $this->suppliers($companyId, $withPhone),
            ],
        ];
    }

    /**
     * @param  callable(?string): bool  $withPhone
     * @return list<array<string, mixed>>
     */
    protected function customers(int $companyId, callable $withPhone): array
    {
        $outstandingIds = CustomerInvoice::query()
            ->where('company_id', $companyId)
            ->where('balance_due', '>', 0)
            ->pluck('customer_id')
            ->flip();

        return Customer::query()
            ->where('company_id', $companyId)
            ->whereNotNull('phone')
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'customer_code', 'phone', 'branch_id', 'customer_type', 'status'])
            ->filter(fn (Customer $c) => $withPhone($c->phone))
            ->map(fn (Customer $c) => [
                'id' => $c->id,
                'label' => $c->company_name.($c->customer_code ? " ({$c->customer_code})" : ''),
                'phone' => $c->phone,
                'branch_id' => $c->branch_id ? (string) $c->branch_id : '',
                'customer_type' => $this->enumValue($c->customer_type),
                'status' => $this->enumValue($c->status),
                'has_outstanding' => $outstandingIds->has($c->id),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  callable(?string): bool  $withPhone
     * @return list<array<string, mixed>>
     */
    protected function leads(int $companyId, callable $withPhone): array
    {
        return Lead::query()
            ->where('company_id', $companyId)
            ->whereNotNull('phone')
            ->orderBy('lead_name')
            ->get(['id', 'lead_name', 'phone', 'branch_id', 'status'])
            ->filter(fn (Lead $l) => $withPhone($l->phone))
            ->map(fn (Lead $l) => [
                'id' => $l->id,
                'label' => $l->lead_name,
                'phone' => $l->phone,
                'branch_id' => $l->branch_id ? (string) $l->branch_id : '',
                'status' => $this->enumValue($l->status),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  callable(?string): bool  $withPhone
     * @return list<array<string, mixed>>
     */
    protected function employees(int $companyId, callable $withPhone): array
    {
        return Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'phone', 'employee_number', 'department_id', 'employment_status'])
            ->filter(fn (Employee $e) => $withPhone($e->phone))
            ->map(fn (Employee $e) => [
                'id' => $e->id,
                'label' => trim("{$e->first_name} {$e->last_name}").($e->employee_number ? " ({$e->employee_number})" : ''),
                'phone' => $e->phone,
                'department_id' => $e->department_id ? (string) $e->department_id : '',
                'employment_status' => $this->enumValue($e->employment_status),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  callable(?string): bool  $withPhone
     * @return list<array<string, mixed>>
     */
    protected function suppliers(int $companyId, callable $withPhone): array
    {
        return Vendor::query()
            ->where('company_id', $companyId)
            ->whereNotNull('phone')
            ->orderBy('vendor_name')
            ->get(['id', 'vendor_name', 'phone', 'vendor_type', 'status'])
            ->filter(fn (Vendor $v) => $withPhone($v->phone))
            ->map(fn (Vendor $v) => [
                'id' => $v->id,
                'label' => $v->vendor_name,
                'phone' => $v->phone,
                'vendor_type' => $this->enumValue($v->vendor_type),
                'status' => $this->enumValue($v->status),
            ])
            ->values()
            ->all();
    }

    protected function enumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return $value !== null && $value !== '' ? (string) $value : '';
    }
}
