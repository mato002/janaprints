<?php

namespace App\Support\Communications\Sms;

use App\Enums\SmsRecipientSource;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Employee;
use App\Models\Procurement\Vendor;
use App\Models\Sales\CustomerInvoice;
use App\Models\User;
use Illuminate\Support\Collection;

class SmsRecipientResolver
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  list<array{phone: string, name?: string, variables?: array<string, string>}>  $manual
     * @return Collection<int, array{source_type: string, source_id: ?int, phone_number: string, display_name: ?string, variable_data: array<string, string>}>
     */
    public function resolve(
        SmsRecipientSource $source,
        int $companyId,
        array $filters = [],
        array $manual = [],
    ): Collection {
        return match ($source) {
            SmsRecipientSource::Customers, SmsRecipientSource::Dynamic => $this->fromCustomers($companyId, $filters),
            SmsRecipientSource::Leads => $this->fromLeads($companyId, $filters),
            SmsRecipientSource::Employees => $this->fromEmployees($companyId, $filters),
            SmsRecipientSource::Suppliers => $this->fromSuppliers($companyId, $filters),
            SmsRecipientSource::Manual, SmsRecipientSource::Imported => $this->fromManual($manual),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function fromCustomers(int $companyId, array $filters): Collection
    {
        $query = Customer::query()->where('company_id', $companyId)->whereNotNull('phone');

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['customer_type'])) {
            $query->where('customer_type', $filters['customer_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['has_outstanding'])) {
            $customerIds = CustomerInvoice::query()
                ->where('company_id', $companyId)
                ->where('balance_due', '>', 0)
                ->pluck('customer_id');
            $query->whereIn('id', $customerIds);
        }

        return $query->get()->map(fn (Customer $c) => [
            'source_type' => 'customer',
            'source_id' => $c->id,
            'phone_number' => $this->normalizePhone($c->phone),
            'display_name' => $c->company_name,
            'variable_data' => [
                'customer_name' => $c->company_name,
                'company_name' => tenant()->company?->name ?? '',
            ],
        ])->filter(fn ($r) => $r['phone_number'] !== '');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function fromLeads(int $companyId, array $filters): Collection
    {
        $query = Lead::query()->where('company_id', $companyId)->whereNotNull('phone');

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get()->map(fn (Lead $l) => [
            'source_type' => 'lead',
            'source_id' => $l->id,
            'phone_number' => $this->normalizePhone($l->phone),
            'display_name' => $l->lead_name,
            'variable_data' => [
                'customer_name' => $l->lead_name,
            ],
        ])->filter(fn ($r) => $r['phone_number'] !== '');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function fromEmployees(int $companyId, array $filters): Collection
    {
        $query = Employee::query()->where('company_id', $companyId)->where('is_active', true)->whereNotNull('phone');

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['employment_status'])) {
            $query->where('employment_status', $filters['employment_status']);
        }

        if (! empty($filters['role'])) {
            $userIds = User::role($filters['role'])->where('company_id', $companyId)->pluck('employee_id')->filter();
            $query->whereIn('id', $userIds);
        }

        return $query->get()->map(function (Employee $e) {
            $name = trim("{$e->first_name} {$e->last_name}");

            return [
                'source_type' => 'employee',
                'source_id' => $e->id,
                'phone_number' => $this->normalizePhone($e->phone),
                'display_name' => $name,
                'variable_data' => [
                    'employee_name' => $name,
                ],
            ];
        })->filter(fn ($r) => $r['phone_number'] !== '');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function fromSuppliers(int $companyId, array $filters): Collection
    {
        $query = Vendor::query()->where('company_id', $companyId)->whereNotNull('phone');

        if (! empty($filters['vendor_type'])) {
            $query->where('vendor_type', $filters['vendor_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get()->map(fn (Vendor $v) => [
            'source_type' => 'vendor',
            'source_id' => $v->id,
            'phone_number' => $this->normalizePhone($v->phone),
            'display_name' => $v->vendor_name,
            'variable_data' => [
                'customer_name' => $v->vendor_name,
            ],
        ])->filter(fn ($r) => $r['phone_number'] !== '');
    }

    /**
     * @param  list<array{phone: string, name?: string, variables?: array<string, string>}>  $manual
     */
    protected function fromManual(array $manual): Collection
    {
        return collect($manual)->map(fn (array $row) => [
            'source_type' => 'manual',
            'source_id' => null,
            'phone_number' => $this->normalizePhone($row['phone'] ?? ''),
            'display_name' => $row['name'] ?? null,
            'variable_data' => $row['variables'] ?? [],
        ])->filter(fn ($r) => $r['phone_number'] !== '');
    }

    protected function normalizePhone(?string $phone): string
    {
        if ($phone === null) {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return strlen($digits) >= 9 ? $digits : '';
    }
}
