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

class SupplierPayablesPostingSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        $mapping = config('posting_account_keys.operating_expense');
        if ($mapping) {
            $account = GlAccount::query()
                ->where('company_id', $company->id)
                ->where('code', $mapping['default_code'])
                ->first();

            if ($account) {
                PostingAccountMapping::query()->updateOrCreate(
                    ['company_id' => $company->id, 'account_key' => 'operating_expense'],
                    ['gl_account_id' => $account->id],
                );
            }
        }

        $this->seedSupplierBillPosted($company->id);
        $this->seedSupplierBillCreditNote($company->id);
        $this->updatePaymentMadeTemplate($company->id);
    }

    protected function seedSupplierBillPosted(int $companyId): void
    {
        if (PostingRule::query()->where('company_id', $companyId)->where('event_code', PostingEventCode::SupplierBillPosted->value)->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $companyId,
            'code' => 'supplier_bill_posted',
            'name' => 'Supplier bill',
            'module' => PostingModule::Procurement,
            'is_active' => true,
            'is_system' => true,
        ]);

        $this->createTemplateLines($template->id, [
            [PostingLineSide::Debit, 'raw_materials', PostingAmountSource::ContextField, 'inventory_amount'],
            [PostingLineSide::Debit, 'operating_expense', PostingAmountSource::ContextField, 'expense_amount'],
            [PostingLineSide::Credit, 'trade_payables', PostingAmountSource::TotalAmount, null],
            [PostingLineSide::Credit, 'vat_payable', PostingAmountSource::TaxAmount, null],
        ]);

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => PostingEventCode::SupplierBillPosted->value,
            'module' => PostingModule::Procurement,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::SupplierBillPosted->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }

    protected function seedSupplierBillCreditNote(int $companyId): void
    {
        if (PostingRule::query()->where('company_id', $companyId)->where('event_code', PostingEventCode::SupplierBillCreditNotePosted->value)->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $companyId,
            'code' => 'supplier_bill_credit_note',
            'name' => 'Supplier credit note',
            'module' => PostingModule::Procurement,
            'is_active' => true,
            'is_system' => true,
        ]);

        $this->createTemplateLines($template->id, [
            [PostingLineSide::Credit, 'raw_materials', PostingAmountSource::ContextField, 'inventory_amount'],
            [PostingLineSide::Credit, 'operating_expense', PostingAmountSource::ContextField, 'expense_amount'],
            [PostingLineSide::Debit, 'trade_payables', PostingAmountSource::TotalAmount, null],
            [PostingLineSide::Debit, 'vat_payable', PostingAmountSource::TaxAmount, null],
        ]);

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => PostingEventCode::SupplierBillCreditNotePosted->value,
            'module' => PostingModule::Procurement,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::SupplierBillCreditNotePosted->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }

    /**
     * @param  list<array{0: PostingLineSide, 1: string, 2: PostingAmountSource, 3: ?string}>  $lines
     */
    protected function createTemplateLines(int $templateId, array $lines): void
    {
        foreach ($lines as $index => [$side, $accountKey, $amountSource, $amountField]) {
            PostingTemplateLine::query()->create([
                'posting_template_id' => $templateId,
                'line_number' => $index + 1,
                'entry_side' => $side,
                'account_resolver' => PostingAccountResolver::AccountKey,
                'account_key' => $accountKey,
                'amount_source' => $amountSource,
                'amount_field' => $amountField,
                'line_description' => ':description',
            ]);
        }
    }

    protected function updatePaymentMadeTemplate(int $companyId): void
    {
        $template = PostingTemplate::query()
            ->where('company_id', $companyId)
            ->where('code', 'payment_made')
            ->first();

        if (! $template) {
            return;
        }

        $template->lines()->delete();

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 1,
            'entry_side' => PostingLineSide::Debit,
            'account_resolver' => PostingAccountResolver::AccountKey,
            'account_key' => 'trade_payables',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 2,
            'entry_side' => PostingLineSide::Credit,
            'account_resolver' => PostingAccountResolver::ContextAccount,
            'context_account_field' => 'payment_account',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);
    }
}
