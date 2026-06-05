<?php

namespace App\Services\Assets;

use App\Enums\ApprovalRuleType;
use App\Enums\AssetDisposalStatus;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDisposal;
use App\Models\Assets\FixedAsset;
use App\Support\Accounting\AssetAccountingPostingService;
use App\Support\Assets\AssetLifecycleService;
use App\Support\Platform\ApprovalRulesService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetDisposalAccountingService
{
    public function __construct(
        protected AssetFinanceTimelineService $timeline,
        protected AssetAccountingPostingService $posting,
        protected ApprovalRulesService $approvals,
        protected AssetPeriodControlService $periodControl,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function dispose(FixedAsset $asset, array $data, int $userId): AssetDisposal
    {
        $requiresApproval = $this->approvals->requiresApproval(
            ApprovalRuleType::AssetDisposalApproval,
            (float) $asset->acquisition_cost,
            null,
            $asset->company_id,
            $asset->branch_id,
        );

        return DB::transaction(function () use ($asset, $data, $userId, $requiresApproval) {
            $nbv = $asset->netBookValue();
            $disposal = AssetLifecycleService::dispose($asset, $data, $userId);

            $disposal->update([
                'nbv_at_disposal' => $nbv,
                'status' => $requiresApproval ? AssetDisposalStatus::PendingApproval : AssetDisposalStatus::Approved,
            ]);

            $this->timeline->record(
                $asset->fresh(),
                'disposed',
                __('Asset disposal recorded'),
                $data['notes'] ?? null,
                $disposal,
                $userId,
            );

            return $disposal->fresh();
        });
    }

    public function approve(AssetDisposal $disposal, int $userId): AssetDisposal
    {
        $disposal->update([
            'status' => AssetDisposalStatus::Approved,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $disposal->fresh();
    }

    public function post(AssetDisposal $disposal, int $userId): AssetDisposal
    {
        if ($disposal->status !== AssetDisposalStatus::Approved) {
            throw ValidationException::withMessages([
                'disposal' => __('Disposal must be approved before posting.'),
            ]);
        }

        if ($disposal->posted_journal_id) {
            return $disposal;
        }

        $asset = $disposal->asset;
        $this->periodControl->assertPeriodOpenForPosting($asset->company_id, $disposal->disposal_date->toDateString());

        return DB::transaction(function () use ($disposal, $asset, $userId) {
            $journal = $this->posting->postDisposal($disposal, $asset, $userId);

            $disposal->update(['status' => AssetDisposalStatus::Posted]);

            $this->timeline->record(
                $asset,
                'posted',
                __('Disposal journal posted'),
                null,
                $disposal,
                $userId,
                ['journal_id' => $journal->id],
            );

            return $disposal->fresh(['journal']);
        });
    }
}
