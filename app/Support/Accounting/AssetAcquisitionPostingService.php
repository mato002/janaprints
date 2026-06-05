<?php

namespace App\Support\Accounting;

use App\Enums\PostingEventCode;
use App\Models\Accounting\Journal;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetPeriodControlService;
use Illuminate\Validation\ValidationException;

class AssetAcquisitionPostingService
{
    public function __construct(
        protected AccountingPostingService $posting,
        protected AssetAccountingPostingService $assetPosting,
        protected AssetPeriodControlService $periodControl,
    ) {}

    public function postAcquisition(FixedAsset $asset, int $userId, string $paymentAccountKey = 'trade_payables'): Journal
    {
        if ($asset->posted_acquisition_journal_id) {
            return Journal::query()->findOrFail($asset->posted_acquisition_journal_id);
        }

        $amount = (float) $asset->acquisition_cost;

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'acquisition' => __('Acquisition cost must be greater than zero.'),
            ]);
        }

        $this->periodControl->assertPeriodOpenForPosting(
            $asset->company_id,
            $asset->capitalization_date?->toDateString() ?? $asset->acquisition_date->toDateString(),
        );

        $asset->load('category', 'vendor', 'purchaseOrder');

        $journal = $this->posting->postEvent(
            PostingEventCode::AssetAcquisitionPosted,
            $asset->company_id,
            $userId,
            'fixed_asset',
            $asset->id,
            $asset->capitalization_date?->toDateString() ?? $asset->acquisition_date->toDateString(),
            ['total_amount' => $amount],
            $asset->branch_id,
            reference: $asset->asset_number,
            description: __('Asset acquisition :asset', ['asset' => $asset->asset_name]),
            accounts: [
                'fixed_asset' => $this->assetPosting->resolveFixedAssetAccountPublic($asset),
                $paymentAccountKey => $this->assetPosting->resolveAccountKeyPublic($asset->company_id, $paymentAccountKey),
            ],
            metadata: [
                'fixed_asset_id' => $asset->id,
                'vendor_id' => $asset->vendor_id,
                'purchase_order_id' => $asset->purchase_order_id,
            ],
        );

        $asset->update(['posted_acquisition_journal_id' => $journal->id]);

        return $journal;
    }
}
