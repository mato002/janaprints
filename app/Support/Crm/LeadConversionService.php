<?php

namespace App\Support\Crm;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\DocumentType;
use App\Enums\LeadStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Support\Platform\NumberingService;
use Illuminate\Validation\ValidationException;

class LeadConversionService
{
    public function convert(Lead $lead): Customer
    {
        if ($lead->customer_id) {
            $existing = Customer::query()->forTenant()->find($lead->customer_id);

            if ($existing) {
                return $existing;
            }
        }

        $companyName = trim($lead->company_name ?: $lead->lead_name);

        if ($companyName === '') {
            throw ValidationException::withMessages([
                'lead' => __('Lead must have a company or lead name before conversion.'),
            ]);
        }

        $customer = Customer::query()->create([
            'company_id' => $lead->company_id,
            'branch_id' => $lead->branch_id,
            'customer_code' => app(NumberingService::class)->next(
                DocumentType::Customer,
                (int) $lead->company_id,
                $lead->branch_id ? (int) $lead->branch_id : null,
            ),
            'customer_type' => CustomerType::Corporate,
            'company_name' => $companyName,
            'contact_person' => $lead->lead_name !== $companyName ? $lead->lead_name : null,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'status' => CustomerStatus::Active,
            'notes' => $lead->notes,
        ]);

        $lead->update([
            'customer_id' => $customer->id,
            'status' => LeadStatus::Won,
        ]);

        return $customer;
    }
}
