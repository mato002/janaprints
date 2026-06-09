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

class AssetCapitalizationPostingRecoveryService
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
                    'recovery_posted',
                    __('Capitalization acquisition journal posted'),
                    __('Recovered via capitalization posting queue.'),
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

            $this->timeline->record(
                $asset->fresh(),
                'recovery_failed',
                __('Capitalization posting recovery failed'),
                $exception->getMessage(),
                null,
                $userId,
            );

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

        if (! in_array($asset->acquisitionAccountingStatus(), [
            AssetAcquisitionAccountingStatus::Failed,
            AssetAcquisitionAccountingStatus::NotPosted,
        ], true)) {
            throw ValidationException::withMessages([
                'acquisition' => __('Capitalization posting recovery cannot be retried for this asset.'),
            ]);
        }

        $asset->update([
            'acquisition_accounting_status' => AssetAcquisitionAccountingStatus::NotPosted,
            'acquisition_posting_error' => null,
        ]);

        return $this->post($asset->fresh(), $userId);
    }

    public function recoveryReason(FixedAsset $asset): string
    {
        if ($asset->acquisition_posting_error) {
            return $asset->acquisition_posting_error;
        }

        return __('Journal was not posted during capitalization (missing post permission or deferred posting).');
    }

    protected function assertEligible(FixedAsset $asset): void
    {
        if ($asset->acquisition_source !== AssetAcquisitionSource::Procurement) {
            throw ValidationException::withMessages([
                'acquisition' => __('Only procurement-capitalized assets appear in the recovery queue.'),
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
