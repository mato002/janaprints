<?php

namespace App\Support\Accounting;

use App\Enums\PostingEventCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\AssetDisposal;
use App\Models\Assets\FixedAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetAccountingPostingService
{
    public function __construct(
        protected AccountingPostingService $posting,
        protected PostingAccountResolverService $accountResolver,
    ) {}

    public function postDepreciation(AssetDepreciationEntry $entry, FixedAsset $asset, int $userId): Journal
    {
        $amount = (float) $entry->depreciation_amount;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'depreciation' => __('Depreciation amount must be greater than zero.'),
            ]);
        }

        $asset->load('category');

        $fixedAssetAccountId = $this->resolveFixedAssetAccount($asset);
        $accumulatedId = $this->resolveAccountKey($asset->company_id, 'accumulated_depreciation');
        $expenseId = $this->resolveAccountKey($asset->company_id, 'depreciation_expense');

        $journal = $this->posting->postEvent(
            PostingEventCode::AssetDepreciationPosted,
            $asset->company_id,
            $userId,
            'asset_depreciation_entry',
            $entry->id,
            $entry->period_date->toDateString(),
            ['total_amount' => $amount],
            $asset->branch_id,
            reference: $asset->asset_number,
            description: __('Depreciation :asset — :period', [
                'asset' => $asset->asset_name,
                'period' => $entry->period_date->format('Y-m'),
            ]),
            accounts: [
                'depreciation_expense' => $expenseId,
                'accumulated_depreciation' => $accumulatedId,
                'fixed_asset' => $fixedAssetAccountId,
            ],
            metadata: ['fixed_asset_id' => $asset->id],
        );

        $entry->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    public function postDisposal(AssetDisposal $disposal, FixedAsset $asset, int $userId): Journal
    {
        return DB::transaction(function () use ($disposal, $asset, $userId) {
            $nbv = $asset->netBookValue();
            $proceeds = (float) $disposal->disposal_proceeds;
            $gainLoss = round($proceeds - $nbv, 2);

            $journal = $this->posting->postEvent(
                PostingEventCode::AssetDisposalPosted,
                $asset->company_id,
                $userId,
                'asset_disposal',
                $disposal->id,
                $disposal->disposal_date->toDateString(),
                [
                    'total_amount' => (float) $asset->acquisition_cost,
                    'amount' => $nbv,
                    'allocated_amount' => $proceeds,
                    'unallocated_amount' => abs($gainLoss),
                ],
                $asset->branch_id,
                reference: $asset->asset_number,
                description: __('Asset disposal :asset', ['asset' => $asset->asset_name]),
                accounts: [
                    'fixed_asset' => $this->resolveFixedAssetAccount($asset),
                    'accumulated_depreciation' => $this->resolveAccountKey($asset->company_id, 'accumulated_depreciation'),
                    'bank' => $this->resolveAccountKey($asset->company_id, 'bank'),
                ],
            );

            $disposal->update([
                'gain_loss_amount' => $gainLoss,
                'posted_journal_id' => $journal->id,
            ]);

            return $journal;
        });
    }

    protected function resolveFixedAssetAccount(FixedAsset $asset): int
    {
        $code = $asset->category?->default_gl_code ?? '1530';

        $account = GlAccount::query()
            ->where('company_id', $asset->company_id)
            ->where('code', $code)
            ->where('is_postable', true)
            ->first();

        if (! $account) {
            throw ValidationException::withMessages([
                'gl_account' => __('Fixed asset GL account :code is not configured.', ['code' => $code]),
            ]);
        }

        return $account->id;
    }

    protected function resolveAccountKey(int $companyId, string $key): int
    {
        return $this->accountResolver->resolveGlAccountId($companyId, $key);
    }
}
