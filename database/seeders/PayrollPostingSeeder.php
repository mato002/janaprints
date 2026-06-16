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

class PayrollPostingSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        foreach (config('posting_account_keys') as $key => $mapping) {
            if (! str_contains($key, 'salary') && ! str_contains($key, 'paye') && ! str_contains($key, 'shif')
                && ! str_contains($key, 'nssf') && ! str_contains($key, 'housing') && ! str_contains($key, 'employer')) {
                continue;
            }

            $account = GlAccount::query()
                ->where('company_id', $company->id)
                ->where('code', $mapping['default_code'])
                ->first();

            if ($account) {
                PostingAccountMapping::query()->updateOrCreate(
                    ['company_id' => $company->id, 'account_key' => $key],
                    ['gl_account_id' => $account->id],
                );
            }
        }

        if (PostingRule::query()->where('company_id', $company->id)->where('event_code', PostingEventCode::PayrollPosted->value)->exists()) {
            return;
        }

        $template = PostingTemplate::query()->create([
            'company_id' => $company->id,
            'code' => 'payroll_posted',
            'name' => 'Payroll accrual',
            'module' => PostingModule::Hr,
            'is_active' => true,
            'is_system' => true,
        ]);

        $lines = [
            [PostingLineSide::Debit, 'salaries_expense', PostingAmountSource::ContextField, 'gross_amount'],
            [PostingLineSide::Debit, 'employer_nssf_expense', PostingAmountSource::ContextField, 'employer_nssf_amount'],
            [PostingLineSide::Debit, 'employer_shif_expense', PostingAmountSource::ContextField, 'employer_shif_amount'],
            [PostingLineSide::Debit, 'employer_housing_levy_expense', PostingAmountSource::ContextField, 'employer_housing_levy_amount'],
            [PostingLineSide::Credit, 'paye_payable', PostingAmountSource::ContextField, 'paye_amount'],
            [PostingLineSide::Credit, 'shif_payable', PostingAmountSource::ContextField, 'shif_amount'],
            [PostingLineSide::Credit, 'nssf_payable', PostingAmountSource::ContextField, 'nssf_amount'],
            [PostingLineSide::Credit, 'housing_levy_payable', PostingAmountSource::ContextField, 'housing_levy_amount'],
            [PostingLineSide::Credit, 'net_salary_payable', PostingAmountSource::ContextField, 'net_amount'],
        ];

        foreach ($lines as $index => [$side, $accountKey, $amountSource, $amountField]) {
            PostingTemplateLine::query()->create([
                'posting_template_id' => $template->id,
                'line_number' => $index + 1,
                'entry_side' => $side,
                'account_resolver' => PostingAccountResolver::AccountKey,
                'account_key' => $accountKey,
                'amount_source' => $amountSource,
                'amount_field' => $amountField,
                'line_description' => ':description',
            ]);
        }

        PostingRule::query()->create([
            'company_id' => $company->id,
            'event_code' => PostingEventCode::PayrollPosted->value,
            'module' => PostingModule::Hr,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::PayrollPosted->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }
}
