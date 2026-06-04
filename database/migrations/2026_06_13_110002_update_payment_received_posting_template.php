<?php

use App\Enums\PostingAccountResolver;
use App\Enums\PostingAmountSource;
use App\Enums\PostingLineSide;
use App\Models\Accounting\PostingTemplate;
use App\Models\Accounting\PostingTemplateLine;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PostingTemplate::query()
            ->where('code', 'payment_received')
            ->each(function (PostingTemplate $template) {
                $template->lines()->delete();

                PostingTemplateLine::query()->create([
                    'posting_template_id' => $template->id,
                    'line_number' => 1,
                    'entry_side' => PostingLineSide::Debit,
                    'account_resolver' => PostingAccountResolver::ContextAccount,
                    'context_account_field' => 'receipt_account',
                    'amount_source' => PostingAmountSource::TotalAmount,
                    'line_description' => ':description',
                ]);

                PostingTemplateLine::query()->create([
                    'posting_template_id' => $template->id,
                    'line_number' => 2,
                    'entry_side' => PostingLineSide::Credit,
                    'account_resolver' => PostingAccountResolver::AccountKey,
                    'account_key' => 'trade_receivables',
                    'amount_source' => PostingAmountSource::AllocatedAmount,
                    'line_description' => ':description',
                ]);

                PostingTemplateLine::query()->create([
                    'posting_template_id' => $template->id,
                    'line_number' => 3,
                    'entry_side' => PostingLineSide::Credit,
                    'account_resolver' => PostingAccountResolver::AccountKey,
                    'account_key' => 'customer_deposits',
                    'amount_source' => PostingAmountSource::UnallocatedAmount,
                    'line_description' => ':description',
                ]);
            });
    }

    public function down(): void
    {
        // Restored by re-running JanaPrintsPostingEngineSeeder on fresh DB only.
    }
};
