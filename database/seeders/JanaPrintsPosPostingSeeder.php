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

class JanaPrintsPosPostingSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            $this->command?->warn('POS posting skipped: JANA company not found.');

            return;
        }

        $this->seedAccountMappings($company->id);
        $this->seedSaleTemplate($company->id, 'pos_sale_cash', PostingEventCode::PosSaleCash, 'cash_till');
        $this->seedSaleTemplate($company->id, 'pos_sale_mpesa', PostingEventCode::PosSaleMpesa, 'mpesa_clearing');
        $this->seedSaleTemplate($company->id, 'pos_sale_card', PostingEventCode::PosSaleCard, 'card_clearing');
        $this->seedSaleTemplate($company->id, 'pos_sale_bank', PostingEventCode::PosSaleBank, 'bank');
        $this->seedReturnTemplate($company->id);
        $this->seedVarianceTemplate($company->id);
    }

    protected function seedAccountMappings(int $companyId): void
    {
        foreach ([
            'cash_till',
            'bank',
            'card_clearing',
            'retail_revenue',
            'sales_returns',
            'mpesa_clearing',
            'cash_shortage_expense',
            'cash_overage_income',
        ] as $key) {
            $config = config("posting_account_keys.{$key}");

            if (! $config || empty($config['default_code'])) {
                continue;
            }

            $account = GlAccount::query()
                ->where('company_id', $companyId)
                ->where('code', $config['default_code'])
                ->first();

            if ($account) {
                PostingAccountMapping::query()->updateOrCreate(
                    ['company_id' => $companyId, 'account_key' => $key],
                    ['gl_account_id' => $account->id],
                );
            }
        }
    }

    protected function seedSaleTemplate(
        int $companyId,
        string $code,
        PostingEventCode $event,
        string $debitAccountKey,
    ): void {
        if (PostingRule::query()->where('company_id', $companyId)->where('event_code', $event->value)->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $event->label(),
            'module' => PostingModule::Pos,
            'description' => __('POS sale GL posting'),
            'is_active' => true,
            'is_system' => true,
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 1,
            'entry_side' => PostingLineSide::Debit,
            'account_resolver' => PostingAccountResolver::AccountKey,
            'account_key' => $debitAccountKey,
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 2,
            'entry_side' => PostingLineSide::Credit,
            'account_resolver' => PostingAccountResolver::AccountKey,
            'account_key' => 'retail_revenue',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => $event->value,
            'module' => PostingModule::Pos,
            'posting_template_id' => $template->id,
            'name' => $event->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }

    protected function seedReturnTemplate(int $companyId): void
    {
        if (PostingRule::query()->where('company_id', $companyId)->where('event_code', PostingEventCode::PosReturn->value)->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $companyId,
            'code' => 'pos_return',
            'name' => PostingEventCode::PosReturn->label(),
            'module' => PostingModule::Pos,
            'description' => __('POS return reversal'),
            'is_active' => true,
            'is_system' => true,
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 1,
            'entry_side' => PostingLineSide::Debit,
            'account_resolver' => PostingAccountResolver::AccountKey,
            'account_key' => 'sales_returns',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingTemplateLine::query()->create([
            'posting_template_id' => $template->id,
            'line_number' => 2,
            'entry_side' => PostingLineSide::Credit,
            'account_resolver' => PostingAccountResolver::ContextAccount,
            'context_account_field' => 'refund_account',
            'amount_source' => PostingAmountSource::TotalAmount,
            'line_description' => ':description',
        ]);

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => PostingEventCode::PosReturn->value,
            'module' => PostingModule::Pos,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::PosReturn->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }

    protected function seedVarianceTemplate(int $companyId): void
    {
        if (PostingRule::query()->where('company_id', $companyId)->where('event_code', PostingEventCode::PosVariance->value)->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $companyId,
            'code' => 'pos_variance',
            'name' => PostingEventCode::PosVariance->label(),
            'module' => PostingModule::Pos,
            'description' => __('POS cash till variance'),
            'is_active' => true,
            'is_system' => true,
        ]);

        $lines = [
            [PostingLineSide::Debit, 'cash_shortage_expense', 'short_amount'],
            [PostingLineSide::Credit, 'cash_till', 'short_amount'],
            [PostingLineSide::Debit, 'cash_till', 'over_amount'],
            [PostingLineSide::Credit, 'cash_overage_income', 'over_amount'],
        ];

        foreach ($lines as $index => [$side, $accountKey, $field]) {
            PostingTemplateLine::query()->create([
                'posting_template_id' => $template->id,
                'line_number' => $index + 1,
                'entry_side' => $side,
                'account_resolver' => PostingAccountResolver::AccountKey,
                'account_key' => $accountKey,
                'amount_source' => PostingAmountSource::ContextField,
                'amount_field' => $field,
                'line_description' => ':description',
            ]);
        }

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => PostingEventCode::PosVariance->value,
            'module' => PostingModule::Pos,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::PosVariance->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }
}
