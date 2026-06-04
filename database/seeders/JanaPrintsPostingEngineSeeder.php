<?php

namespace Database\Seeders;

use App\Enums\PostingAccountResolver;
use App\Enums\PostingAmountSource;
use App\Enums\PostingEventCode;
use App\Enums\PostingLineSide;
use App\Enums\PostingModule;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\PostingAccountMapping;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplate;
use App\Models\Accounting\PostingTemplateLine;
use App\Models\Company;
use Illuminate\Database\Seeder;

class JanaPrintsPostingEngineSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            $this->command?->warn('Posting engine skipped: JANA company not found.');

            return;
        }

        if (PostingTemplate::query()->where('company_id', $company->id)->exists()) {
            $this->command?->warn('Posting engine already seeded for JANA.');

            return;
        }

        $this->seedAccountMappings($company->id);
        $this->seedTemplatesAndRules($company->id);
    }

    protected function seedAccountMappings(int $companyId): void
    {
        foreach (config('posting_account_keys', []) as $key => $meta) {
            $account = GlAccount::query()
                ->where('company_id', $companyId)
                ->where('code', $meta['default_code'])
                ->first();

            if ($account) {
                PostingAccountMapping::query()->updateOrCreate(
                    ['company_id' => $companyId, 'account_key' => $key],
                    ['gl_account_id' => $account->id],
                );
            }
        }
    }

    protected function seedTemplatesAndRules(int $companyId): void
    {
        $definitions = [
            'inventory_receipt' => [
                'module' => PostingModule::Inventory,
                'name' => 'Inventory receipt',
                'lines' => [
                    ['debit', 'raw_materials', PostingAmountSource::TotalAmount],
                    ['credit', 'trade_payables', PostingAmountSource::TotalAmount],
                ],
                'events' => [
                    PostingEventCode::InventoryReceiptPosted,
                ],
            ],
            'inventory_issue' => [
                'module' => PostingModule::Inventory,
                'name' => 'Inventory issue to production',
                'lines' => [
                    ['debit', 'wip', PostingAmountSource::TotalAmount],
                    ['credit', 'raw_materials', PostingAmountSource::TotalAmount],
                ],
                'events' => [
                    PostingEventCode::InventoryIssuePosted,
                    PostingEventCode::ProductionMaterialConsumptionPosted,
                ],
            ],
            'inventory_adjustment' => [
                'module' => PostingModule::Inventory,
                'name' => 'Inventory adjustment',
                'lines' => [
                    ['debit', 'raw_materials', PostingAmountSource::Amount],
                    ['credit', 'material_consumption', PostingAmountSource::Amount],
                ],
                'events' => [PostingEventCode::InventoryAdjustmentPosted],
            ],
            'inventory_consumption' => [
                'module' => PostingModule::Inventory,
                'name' => 'Inventory consumption',
                'lines' => [
                    ['debit', 'material_consumption', PostingAmountSource::TotalAmount],
                    ['credit', 'raw_materials', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::InventoryConsumptionPosted],
            ],
            'procurement_grn' => [
                'module' => PostingModule::Procurement,
                'name' => 'Goods receipt',
                'lines' => [
                    ['debit', 'raw_materials', PostingAmountSource::TotalAmount],
                    ['credit', 'trade_payables', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::ProcurementGoodsReceiptPosted],
            ],
            'procurement_invoice' => [
                'module' => PostingModule::Procurement,
                'name' => 'Supplier invoice',
                'lines' => [
                    ['debit', 'raw_materials', PostingAmountSource::TotalAmount],
                    ['credit', 'trade_payables', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::ProcurementInvoiceReceived],
            ],
            'production_completion' => [
                'module' => PostingModule::Production,
                'name' => 'Production completion',
                'lines' => [
                    ['debit', 'finished_goods', PostingAmountSource::TotalAmount],
                    ['credit', 'wip', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::ProductionCompletionPosted],
            ],
            'sales_revenue' => [
                'module' => PostingModule::Sales,
                'name' => 'Sales revenue recognition',
                'lines' => [
                    ['debit', 'trade_receivables', PostingAmountSource::TotalAmount],
                    ['credit', 'printing_revenue', PostingAmountSource::Subtotal],
                    ['credit', 'vat_payable', PostingAmountSource::TaxAmount],
                ],
                'events' => [
                    PostingEventCode::SalesOrderConfirmed,
                    PostingEventCode::SalesRevenueRecognized,
                ],
            ],
            'invoice_posted' => [
                'module' => PostingModule::Invoice,
                'name' => 'Customer invoice',
                'lines' => [
                    ['debit', 'trade_receivables', PostingAmountSource::TotalAmount],
                    ['credit', 'printing_revenue', PostingAmountSource::Subtotal],
                    ['credit', 'vat_payable', PostingAmountSource::TaxAmount],
                ],
                'events' => [PostingEventCode::InvoicePosted],
            ],
            'invoice_credit_note' => [
                'module' => PostingModule::Invoice,
                'name' => 'Credit note',
                'lines' => [
                    ['credit', 'trade_receivables', PostingAmountSource::TotalAmount],
                    ['debit', 'printing_revenue', PostingAmountSource::Subtotal],
                    ['debit', 'vat_payable', PostingAmountSource::TaxAmount],
                ],
                'events' => [PostingEventCode::InvoiceCreditNotePosted],
            ],
            'payment_received' => [
                'module' => PostingModule::Payment,
                'name' => 'Customer payment received',
                'lines' => 'payment_received_split',
                'events' => [PostingEventCode::PaymentReceived],
            ],
            'payment_made' => [
                'module' => PostingModule::Payment,
                'name' => 'Supplier payment',
                'lines' => [
                    ['debit', 'trade_payables', PostingAmountSource::TotalAmount],
                    ['credit', 'bank', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::PaymentMade],
            ],
            'payment_refund' => [
                'module' => PostingModule::Payment,
                'name' => 'Payment refund',
                'lines' => [
                    ['debit', 'trade_receivables', PostingAmountSource::TotalAmount],
                    ['credit', 'bank', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::PaymentRefundPosted],
            ],
            'supplier_bill' => [
                'module' => PostingModule::Procurement,
                'name' => 'Supplier bill',
                'lines' => [
                    ['debit', 'operating_expense', PostingAmountSource::Subtotal],
                    ['debit', 'vat_payable', PostingAmountSource::TaxAmount],
                    ['credit', 'trade_payables', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::SupplierBillPosted],
            ],
            'supplier_bill_credit_note' => [
                'module' => PostingModule::Procurement,
                'name' => 'Supplier bill credit note',
                'lines' => [
                    ['credit', 'operating_expense', PostingAmountSource::Subtotal],
                    ['credit', 'vat_payable', PostingAmountSource::TaxAmount],
                    ['debit', 'trade_payables', PostingAmountSource::TotalAmount],
                ],
                'events' => [PostingEventCode::SupplierBillCreditNotePosted],
            ],
        ];

        foreach ($definitions as $code => $def) {
            $template = PostingTemplate::query()->create([
                'company_id' => $companyId,
                'code' => $code,
                'name' => $def['name'],
                'module' => $def['module'],
                'description' => __('System posting template for :name', ['name' => $def['name']]),
                'is_active' => true,
                'is_system' => true,
            ]);

            if (($def['lines'] ?? null) === 'payment_received_split') {
                $this->seedPaymentReceivedTemplateLines($template->id);
            } else {
                foreach ($def['lines'] as $index => [$side, $accountKey, $amountSource]) {
                    PostingTemplateLine::query()->create([
                        'posting_template_id' => $template->id,
                        'line_number' => $index + 1,
                        'entry_side' => $side === 'debit' ? PostingLineSide::Debit : PostingLineSide::Credit,
                        'account_resolver' => PostingAccountResolver::AccountKey,
                        'account_key' => $accountKey,
                        'amount_source' => $amountSource,
                        'line_description' => ':description',
                    ]);
                }
            }

            foreach ($def['events'] as $event) {
                PostingRule::query()->create([
                    'company_id' => $companyId,
                    'event_code' => $event->value,
                    'module' => $event->module(),
                    'posting_template_id' => $template->id,
                    'name' => $event->label(),
                    'description' => __('Auto-posting rule for :event', ['event' => $event->label()]),
                    'priority' => 100,
                    'is_active' => true,
                    'auto_post' => true,
                    'is_system' => true,
                ]);
            }
        }

        app(SupplierPayablesPostingSeeder::class)->run();
    }

    protected function seedPaymentReceivedTemplateLines(int $templateId): void
    {
        PostingTemplateLine::query()->create([
            'posting_template_id' => $templateId,
            'line_number' => 1,
            'entry_side' => PostingLineSide::Debit,
            'account_resolver' => PostingAccountResolver::ContextAccount,
            'context_account_field' => 'receipt_account',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $templateId,
            'line_number' => 2,
            'entry_side' => PostingLineSide::Credit,
            'account_resolver' => PostingAccountResolver::AccountKey,
            'account_key' => 'trade_receivables',
            'amount_source' => PostingAmountSource::AllocatedAmount,
            'line_description' => ':description',
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $templateId,
            'line_number' => 3,
            'entry_side' => PostingLineSide::Credit,
            'account_resolver' => PostingAccountResolver::AccountKey,
            'account_key' => 'customer_deposits',
            'amount_source' => PostingAmountSource::UnallocatedAmount,
            'line_description' => ':description',
        ]);
    }
}
