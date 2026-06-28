<?php

namespace App\Services\Crm;

use App\Enums\DocumentType;
use App\Enums\QuotationStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Crm\LeadConversionService;
use App\Support\Platform\NumberingService;
use App\Support\Platform\SystemSettingsService;
use App\Support\QuotationRevisionService;
use App\Support\Sales\QuotationApprovalService;
use Illuminate\Validation\ValidationException;

class LeadQuotationService
{
    public function __construct(
        protected LeadConversionService $conversion,
        protected SystemSettingsService $settings,
        protected NumberingService $numbering,
    ) {}

    public function autoConvertEnabled(int $companyId): bool
    {
        return (bool) $this->settings->get('auto_convert_lead_on_quote', true, $companyId, null);
    }

    public function resolveCustomer(Lead $lead, User $actor): Customer
    {
        if ($lead->customer_id) {
            $customer = Customer::query()->forTenant()->find($lead->customer_id);

            if ($customer) {
                return $customer;
            }
        }

        if (! $this->autoConvertEnabled((int) $lead->company_id)) {
            throw ValidationException::withMessages([
                'lead' => __('Convert this lead to a customer before creating a quotation, or enable automatic lead conversion in CRM settings.'),
            ]);
        }

        if (! $actor->can('convert', $lead)) {
            throw ValidationException::withMessages([
                'lead' => __('You do not have permission to convert this lead to a customer.'),
            ]);
        }

        return $this->conversion->convert($lead);
    }

    public function createDraftQuotation(Lead $lead, User $actor): Quotation
    {
        $customer = $this->resolveCustomer($lead, $actor);
        $lead->refresh();

        $validityDays = (int) $this->settings->get(
            'quotation_validity_days',
            30,
            (int) $lead->company_id,
            $lead->branch_id ? (int) $lead->branch_id : null,
        );

        $currency = (string) $this->settings->get(
            'default_currency',
            'KES',
            (int) $lead->company_id,
            null,
        );

        $quotation = Quotation::query()->create([
            'company_id' => $lead->company_id,
            'branch_id' => $lead->branch_id,
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'quotation_number' => $this->numbering->next(
                DocumentType::Quotation,
                (int) $lead->company_id,
                $lead->branch_id ? (int) $lead->branch_id : null,
            ),
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(max(1, $validityDays))->toDateString(),
            'currency' => $currency,
            'notes' => $lead->notes,
            'prepared_by' => $actor->id,
            'status' => QuotationStatus::Draft,
            'revision_number' => 1,
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
        ]);

        QuotationRevisionService::snapshot($quotation);

        return app(QuotationApprovalService::class)->publishOnCreate($quotation->fresh(), $actor->id);
    }
}
