<?php

namespace App\Services\Assets;

use App\Enums\AssetAcquisitionAccountingStatus;
use App\Enums\AssetAcquisitionSource;
use App\Models\Accounting\Journal;
use App\Models\Assets\FixedAsset;
use App\Support\Accounting\AssetAcquisitionPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class AssetManualAcquisitionPostingService
{
    public function __construct(
        protected AssetAcquisitionPostingService $posting,
        protected AssetFinanceTimelineService $timeline,
    ) {}

    public function post(FixedAsset $asset, int $userId): Journal
    {
        $this->assertEligible($asset);

        try {
            return DB::transaction(function () use ($asset, $userId) {
                $journal = $this->posting->postAcquisition($asset->fresh(), $userId);

                $asset->update([
                    'acquisition_accounting_status' => AssetAcquisitionAccountingStatus::Posted,
                    'acquisition_posting_error' => null,
                ]);

                $this->timeline->record(
                    $asset->fresh(),
                    'posted',
                    __('Acquisition journal posted'),
                    null,
                    $journal,
                    $userId,
                );

                return $journal;
            });
        } catch (Throwable $exception) {
            $asset->update([
                'acquisition_accounting_status' => AssetAcquisitionAccountingStatus::Failed,
                'acquisition_posting_error' => $exception->getMessage(),
            ]);

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'acquisition' => $exception->getMessage(),
            ]);
        }
    }

    public function retry(FixedAsset $asset, int $userId): Journal
    {
        if ($asset->posted_acquisition_journal_id) {
            return Journal::query()->findOrFail($asset->posted_acquisition_journal_id);
        }

        if ($asset->acquisition_accounting_status !== AssetAcquisitionAccountingStatus::Failed
            && $asset->acquisition_accounting_status !== AssetAcquisitionAccountingStatus::NotPosted) {
            throw ValidationException::withMessages([
                'acquisition' => __('Acquisition posting cannot be retried for this asset.'),
            ]);
        }

        $asset->update([
            'acquisition_accounting_status' => AssetAcquisitionAccountingStatus::NotPosted,
            'acquisition_posting_error' => null,
        ]);

        return $this->post($asset->fresh(), $userId);
    }

    public function markPending(FixedAsset $asset): FixedAsset
    {
        if ($asset->posted_acquisition_journal_id) {
            return $asset;
        }

        $asset->update([
            'acquisition_accounting_status' => AssetAcquisitionAccountingStatus::NotPosted,
            'acquisition_posting_error' => null,
        ]);

        return $asset->fresh();
    }

    protected function assertEligible(FixedAsset $asset): void
    {
        if ($asset->acquisition_source !== AssetAcquisitionSource::Manual) {
            throw ValidationException::withMessages([
                'acquisition' => __('Only manually registered assets can be posted from the asset register.'),
            ]);
        }

        if ($asset->posted_acquisition_journal_id) {
            throw ValidationException::withMessages([
                'acquisition' => __('Acquisition journal has already been posted.'),
            ]);
        }

        if ((float) $asset->acquisition_cost <= 0) {
            throw ValidationException::withMessages([
                'acquisition' => __('Acquisition cost must be greater than zero.'),
            ]);
        }
    }
}
