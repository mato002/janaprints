<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentStatus;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\User;

class CustomerFinancialWorkspaceService
{
    public function __construct(
        protected CustomerFinancialIntelligenceService $intelligence,
        protected CustomerStatementService $statements,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function build(Customer $customer, User $user, array $query = []): array
    {
        $canInvoices = $user->can('invoices.view');
        $canPayments = $user->can('payments.view');
        $canStatement = $user->can('receivables.statement.view');
        $canReceipts = $user->can('payments.receipt.view');

        if (! $canInvoices && ! $canPayments && ! $canStatement) {
            return ['restricted' => true];
        }

        $section = $query['financial_section'] ?? 'overview';
        $profile = $this->intelligence->profile($customer);

        $data = [
            'restricted' => false,
            'section' => $section,
            'profile' => $profile,
            'can_invoices' => $canInvoices,
            'can_payments' => $canPayments,
            'can_statement' => $canStatement,
            'can_receipts' => $canReceipts,
        ];

        if ($canInvoices) {
            $data['invoices'] = CustomerInvoice::query()
                ->where('customer_id', $customer->id)
                ->where('company_id', $customer->company_id)
                ->whereNot('invoice_type', CustomerInvoiceType::CreditNote)
                ->latest('invoice_date')
                ->paginate(15, pageName: 'invoices_page');

            $data['credit_notes'] = CustomerInvoice::query()
                ->where('customer_id', $customer->id)
                ->where('company_id', $customer->company_id)
                ->where('invoice_type', CustomerInvoiceType::CreditNote)
                ->latest('invoice_date')
                ->paginate(15, pageName: 'credit_notes_page');
        }

        if ($canPayments) {
            $data['payments'] = CustomerPayment::query()
                ->where('customer_id', $customer->id)
                ->where('company_id', $customer->company_id)
                ->withSum('allocations as allocated_sum', 'amount')
                ->latest('payment_date')
                ->paginate(15, pageName: 'payments_page');

            $data['deposits'] = $profile['credit_wallet']['deposits'];

            if ($canReceipts) {
                $data['receipts'] = CustomerPayment::query()
                    ->where('customer_id', $customer->id)
                    ->where('company_id', $customer->company_id)
                    ->where('status', CustomerPaymentStatus::Posted)
                    ->whereNotNull('receipt_number')
                    ->latest('payment_date')
                    ->paginate(15, pageName: 'receipts_page');
            }
        }

        if ($section === 'statement' && $canStatement) {
            $from = $query['statement_from'] ?? now()->subMonths(3)->toDateString();
            $to = $query['statement_to'] ?? now()->toDateString();

            $data['statement_from'] = $from;
            $data['statement_to'] = $to;
            $data['statement'] = $this->statements->build([
                'customer_id' => $customer->id,
                'from_date' => $from,
                'to_date' => $to,
            ]);
        }

        return $data;
    }
}
