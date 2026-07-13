<?php

namespace App\Support\Accounting;

use App\Enums\PostingEventCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\AssetDisposal;
use App\Models\Assets\AssetWriteOff;
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
        if ($entry->posted_journal_id) {
            return Journal::query()->findOrFail($entry->posted_journal_id);
        }

        $amount = (float) $entry->depreciation_amount;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'depreciation' => __('Depreciation amount must be greater than zero.'),
            ]);
        }

        $asset->load('category');

        $fixedAssetAccountId = $this->resolveFixedAssetAccount($asset);
        $accumulatedId = $this->resolveCategoryAccount($asset, 'accumulated_depreciation_gl_code', 'accumulated_depreciation');
        $expenseId = $this->resolveCategoryAccount($asset, 'depreciation_expense_gl_code', 'depreciation_expense');

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
        if ($disposal->posted_journal_id) {
            return Journal::query()->findOrFail($disposal->posted_journal_id);
        }

        return DB::transaction(function () use ($disposal, $asset, $userId) {
            $nbv = (float) ($disposal->nbv_at_disposal ?? $asset->netBookValue());
            $cost = (float) $asset->acquisition_cost;
            $proceeds = (float) $disposal->disposal_proceeds;
            $gainLoss = round($proceeds - $nbv, 2);
            $accumulated = round(max(0, $cost - $nbv), 2);

            $journal = $this->posting->postEvent(
                PostingEventCode::AssetDisposalPosted,
                $asset->company_id,
                $userId,
                'asset_disposal',
                $disposal->id,
                $disposal->disposal_date->toDateString(),
                [
                    'total_amount' => $cost,
                    'amount' => $nbv,
                    'allocated_amount' => $proceeds,
                    'unallocated_amount' => abs($gainLoss),
                    'accumulated_amount' => $accumulated,
                    'gain_amount' => max(0, $gainLoss),
                    'loss_amount' => max(0, -$gainLoss),
                ],
                $asset->branch_id,
                reference: $asset->asset_number,
                description: __('Asset disposal :asset', ['asset' => $asset->asset_name]),
                accounts: [
                    'fixed_asset' => $this->resolveFixedAssetAccount($asset),
                    'accumulated_depreciation' => $this->resolveCategoryAccount($asset, 'accumulated_depreciation_gl_code', 'accumulated_depreciation'),
                    'bank' => $this->resolveAccountKey($asset->company_id, 'bank'),
                    'asset_disposal_gain' => $this->resolveCategoryAccount($asset, 'asset_gain_loss_gl_code', 'asset_disposal_gain'),
                    'asset_disposal_loss' => $this->resolveCategoryAccount($asset, 'asset_gain_loss_gl_code', 'asset_disposal_loss'),
                ],
            );

            $disposal->update([
                'gain_loss_amount' => $gainLoss,
                'posted_journal_id' => $journal->id,
            ]);

            return $journal;
        });
    }

    public function postWriteOff(AssetWriteOff $writeOff, FixedAsset $asset, int $userId): Journal
    {
        if ($writeOff->posted_journal_id) {
            return Journal::query()->findOrFail($writeOff->posted_journal_id);
        }

        $amount = (float) $writeOff->nbv_at_writeoff;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'writeoff' => __('Write-off amount must be greater than zero.'),
            ]);
        }

        $asset->load('category');
        $cost = (float) $asset->acquisition_cost;
        $accumulated = round(max(0, $cost - $amount), 2);

        $journal = $this->posting->postEvent(
            PostingEventCode::AssetWriteOffPosted,
            $asset->company_id,
            $userId,
            'asset_write_off',
            $writeOff->id,
            $writeOff->write_off_date->toDateString(),
            [
                'total_amount' => $cost,
                'amount' => $amount,
                'accumulated_amount' => $accumulated,
            ],
            $asset->branch_id,
            reference: $writeOff->writeoff_no,
            description: __('Asset write-off :asset', ['asset' => $asset->asset_name]),
            accounts: [
                'asset_disposal_loss' => $this->resolveCategoryAccount($asset, 'asset_gain_loss_gl_code', 'asset_disposal_loss'),
                'fixed_asset' => $this->resolveFixedAssetAccount($asset),
                'accumulated_depreciation' => $this->resolveCategoryAccount($asset, 'accumulated_depreciation_gl_code', 'accumulated_depreciation'),
            ],
            metadata: ['fixed_asset_id' => $asset->id],
        );

        $writeOff->update(['posted_journal_id' => $journal->id]);

        return $journal;
    }

    public function resolveFixedAssetAccountPublic(FixedAsset $asset): int
    {
        return $this->resolveFixedAssetAccount($asset);
    }

    public function resolveAccountKeyPublic(int $companyId, string $key): int
    {
        return $this->resolveAccountKey($companyId, $key);
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

    protected function resolveCategoryAccount(FixedAsset $asset, string $categoryField, string $fallbackKey): int
    {
        $code = $asset->category?->{$categoryField};

        if ($code) {
            $account = GlAccount::query()
                ->where('company_id', $asset->company_id)
                ->where('code', $code)
                ->where('is_postable', true)
                ->first();

            if ($account) {
                return (int) $account->id;
            }
        }

        return $this->resolveAccountKey($asset->company_id, $fallbackKey);
    }

    protected function resolveAccountKey(int $companyId, string $key): int
    {
        return $this->accountResolver->resolveGlAccountId($companyId, $key);
    }
}
