<?php

namespace App\Support\Crm;

use App\Enums\DocumentType;
use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Platform\NumberingService;
use App\Support\QuotationRevisionService;
use Illuminate\Validation\ValidationException;

class LeadQuotationService
{
    public function __construct(
        protected CrmSettings $crmSettings,
        protected LeadConversionService $leadConversion,
    ) {}

    public function autoConvertEnabled(Lead $lead): bool
    {
        return $this->crmSettings->autoConvertLeadOnQuote($lead->company_id, $lead->branch_id);
    }

    public function canCreateFromLead(User $user, Lead $lead): bool
    {
        if (! $user->can('quotations.create') || ! $user->can('crm.leads.view')) {
            return false;
        }

        if ($lead->customer_id) {
            return true;
        }

        return $this->autoConvertEnabled($lead) && $user->can('crm.customers.create');
    }

    /**
     * @return array{customer_id: int, lead_id: int}
     */
    public function resolveQuotationContext(Lead $lead, User $user): array
    {
        $this->assertCanCreateFromLead($lead, $user);

        $customer = $this->resolveCustomer($lead, $user);

        return [
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
        ];
    }

    public function quickQuote(Lead $lead, User $user): Quotation
    {
        $context = $this->resolveQuotationContext($lead, $user);

        $quotation = Quotation::query()->create([
            'company_id' => $lead->company_id,
            'branch_id' => $lead->branch_id,
            'customer_id' => $context['customer_id'],
            'lead_id' => $context['lead_id'],
            'quotation_number' => app(NumberingService::class)->next(
                DocumentType::Quotation,
                $lead->company_id,
                $lead->branch_id,
            ),
            'quotation_date' => now()->toDateString(),
            'currency' => 'KES',
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Draft,
            'revision_number' => 1,
            'subtotal' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'notes' => __('Quick quote from lead :name', ['name' => $lead->lead_name]),
        ]);

        $quotation->items()->create([
            'item_type' => QuotationItemType::Service,
            'item_name' => __('Quotation line'),
            'description' => __('Created from lead :name', ['name' => $lead->lead_name]),
            'quantity' => 1,
            'unit_price' => (float) $lead->estimated_value > 0 ? $lead->estimated_value : 0,
            'discount' => 0,
            'tax_rate' => 0,
            'line_total' => (float) $lead->estimated_value > 0 ? $lead->estimated_value : 0,
            'sort_order' => 0,
        ]);

        if ((float) $lead->estimated_value > 0) {
            $quotation->update([
                'subtotal' => $lead->estimated_value,
                'total_amount' => $lead->estimated_value,
            ]);
        }

        QuotationRevisionService::snapshot($quotation, $user->id);
        ActivityLogger::log('quote_created_from_lead', $quotation, $user->id, [
            'lead_id' => $lead->id,
            'quick_quote' => true,
        ]);

        return $quotation->fresh(['customer', 'lead', 'items']);
    }

    protected function resolveCustomer(Lead $lead, User $user): Customer
    {
        if ($lead->customer_id) {
            $customer = Customer::query()->forTenant()->find($lead->customer_id);

            if ($customer) {
                return $customer;
            }
        }

        if (! $this->autoConvertEnabled($lead)) {
            throw ValidationException::withMessages([
                'lead' => __('Convert this lead to a customer before creating a quotation, or enable auto-convert in CRM settings.'),
            ]);
        }

        if (! $user->can('crm.customers.create')) {
            throw ValidationException::withMessages([
                'lead' => __('You need customer creation permission to auto-convert this lead.'),
            ]);
        }

        return $this->leadConversion->ensureCustomerForQuotation($lead->fresh());
    }

    protected function assertCanCreateFromLead(Lead $lead, User $user): void
    {
        if (! $this->canCreateFromLead($user, $lead)) {
            throw ValidationException::withMessages([
                'lead' => __('You are not allowed to create a quotation from this lead.'),
            ]);
        }

        if ($lead->status === \App\Enums\LeadStatus::Lost) {
            throw ValidationException::withMessages([
                'lead' => __('Cannot create quotations for a lost lead.'),
            ]);
        }
    }
}
