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
use Database\Seeders\JanaPrintsPosPostingSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $company = Company::query()->where('code', 'JANA')->first();

        if (! $company) {
            return;
        }

        (new JanaPrintsPosPostingSeeder)->run();
        $this->seedAssetDisposal($company->id);
        $this->repairAssetWriteOff($company->id);
        $this->deactivateDuplicateRevenueRules($company->id);
    }

    public function down(): void
    {
        // Additive seed — keep templates on rollback.
    }

    protected function seedAssetDisposal(int $companyId): void
    {
        if (PostingRule::query()
            ->where('company_id', $companyId)
            ->where('event_code', PostingEventCode::AssetDisposalPosted->value)
            ->exists()) {
            return;
        }

        $template = PostingTemplate::query()->updateOrCreate(
            ['company_id' => $companyId, 'code' => 'asset_disposal'],
            [
                'name' => 'Asset disposal',
                'module' => PostingModule::Assets,
                'description' => __('Clear asset and accum dep; recognize proceeds and gain/loss'),
                'is_active' => true,
                'is_system' => true,
            ],
        );

        if (! $template->lines()->exists()) {
            $lines = [
                [PostingLineSide::Debit, 'accumulated_depreciation', PostingAmountSource::ContextField, 'accumulated_amount'],
                [PostingLineSide::Debit, 'bank', PostingAmountSource::AllocatedAmount, null],
                [PostingLineSide::Debit, 'asset_disposal_loss', PostingAmountSource::ContextField, 'loss_amount'],
                [PostingLineSide::Credit, 'asset_disposal_gain', PostingAmountSource::ContextField, 'gain_amount'],
                [PostingLineSide::Credit, 'fixed_asset', PostingAmountSource::TotalAmount, null],
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
        }

        PostingRule::query()->create([
            'company_id' => $companyId,
            'event_code' => PostingEventCode::AssetDisposalPosted->value,
            'module' => PostingModule::Assets,
            'posting_template_id' => $template->id,
            'name' => PostingEventCode::AssetDisposalPosted->label(),
            'priority' => 100,
            'is_active' => true,
            'auto_post' => true,
            'is_system' => true,
        ]);
    }

    protected function repairAssetWriteOff(int $companyId): void
    {
        $template = PostingTemplate::query()
            ->where('company_id', $companyId)
            ->where('code', 'asset_writeoff')
            ->first();

        if (! $template) {
            return;
        }

        $template->lines()->delete();

        $lines = [
            [PostingLineSide::Debit, 'accumulated_depreciation', PostingAmountSource::ContextField, 'accumulated_amount'],
            [PostingLineSide::Debit, 'asset_disposal_loss', PostingAmountSource::Amount, null],
            [PostingLineSide::Credit, 'fixed_asset', PostingAmountSource::TotalAmount, null],
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
    }

    /**
     * Revenue posts via invoice.posted / supplier_bill.posted — keep unused event codes inactive.
     */
    protected function deactivateDuplicateRevenueRules(int $companyId): void
    {
        PostingRule::query()
            ->where('company_id', $companyId)
            ->whereIn('event_code', [
                PostingEventCode::SalesOrderConfirmed->value,
                PostingEventCode::SalesRevenueRecognized->value,
                PostingEventCode::ProcurementInvoiceReceived->value,
            ])
            ->update([
                'is_active' => false,
                'description' => __('Inactive — operational posting uses invoice.posted / supplier_bill.posted to avoid double-counting.'),
            ]);
    }
};
