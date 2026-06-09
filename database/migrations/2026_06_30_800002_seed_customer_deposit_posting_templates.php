<?php

use App\Enums\PostingAccountResolver;
use App\Enums\PostingAmountSource;
use App\Enums\PostingEventCode;
use App\Enums\PostingLineSide;
use App\Enums\PostingModule;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplate;
use App\Models\Accounting\PostingTemplateLine;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        $this->seedDepositApplicationTemplate($company->id);
        $this->seedDepositRefundTemplate($company->id);
    }

    public function down(): void
    {
        // Templates remain; no destructive rollback.
    }

    protected function seedDepositApplicationTemplate(int $companyId): void
    {
        if (PostingRule::query()
            ->where('company_id', $companyId)
            ->where('event_code', PostingEventCode::DepositApplicationPosted->value)
            ->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $companyId,
            'code' => 'deposit_application',
            'name' => 'Customer deposit applied',
            'module' => PostingModule::Payment,
            'description' => __('DR customer deposits, CR trade receivables'),
            'is_active' => true,
            'is_system' => true,
        ]);

        $lines = [
            [PostingLineSide::Debit, 'customer_deposits', PostingAmountSource::TotalAmount],
            [PostingLineSide::Credit, 'trade_receivables', PostingAmountSource::TotalAmount],
        ];

        foreach ($lines as $index => [$side, $accountKey, $amountSource]) {
            PostingTemplateLine::query()->create([
                'posting_template_id' => $template->id,
                'line_number' => $index + 1,
                'entry_side' => $side,
                'account_resolver' => PostingAccountResolver::AccountKey,
                'account_key' => $accountKey,
                'amount_source' => $amountSource,
                'line_description' => ':description',
            ]);
        }

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => PostingEventCode::DepositApplicationPosted->value,
            'module' => PostingModule::Payment,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::DepositApplicationPosted->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }

    protected function seedDepositRefundTemplate(int $companyId): void
    {
        if (PostingRule::query()
            ->where('company_id', $companyId)
            ->where('event_code', PostingEventCode::DepositRefundPosted->value)
            ->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $companyId,
            'code' => 'deposit_refund',
            'name' => 'Customer deposit refund',
            'module' => PostingModule::Payment,
            'description' => __('DR customer deposits, CR receipt account'),
            'is_active' => true,
            'is_system' => true,
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 1,
            'entry_side' => PostingLineSide::Debit,
            'account_resolver' => PostingAccountResolver::AccountKey,
            'account_key' => 'customer_deposits',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 2,
            'entry_side' => PostingLineSide::Credit,
            'account_resolver' => PostingAccountResolver::ContextAccount,
            'context_account_field' => 'receipt_account',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => PostingEventCode::DepositRefundPosted->value,
            'module' => PostingModule::Payment,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::DepositRefundPosted->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }
};
