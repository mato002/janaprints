<?php

namespace App\Services\Assets;

use App\Enums\ApprovalRuleType;
use App\Enums\AssetWriteOffStatus;
use App\Enums\DocumentType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetWriteOff;
use App\Models\Assets\FixedAsset;
use App\Support\Accounting\AssetAccountingPostingService;
use App\Support\Platform\ApprovalRulesService;
use App\Support\Platform\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetWriteOffService
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
    public function create(FixedAsset $asset, array $data, int $userId): AssetWriteOff
    {
        if ($asset->status === FixedAssetStatus::Disposed) {
            throw ValidationException::withMessages([
                'asset' => __('Asset is already disposed.'),
            ]);
        }

        $requiresApproval = $this->approvals->requiresApproval(
            ApprovalRuleType::AssetWriteOffApproval,
            (float) $asset->acquisition_cost,
            null,
            $asset->company_id,
            $asset->branch_id,
        );

        return AssetWriteOff::query()->create([
            'company_id' => $asset->company_id,
            'fixed_asset_id' => $asset->id,
            'writeoff_no' => app(NumberGenerator::class)->generate(DocumentType::AssetWriteOff, $asset->company_id),
            'reason' => $data['reason'],
            'write_off_date' => $data['write_off_date'] ?? now()->toDateString(),
            'nbv_at_writeoff' => $asset->netBookValue(),
            'status' => $requiresApproval ? AssetWriteOffStatus::PendingApproval : AssetWriteOffStatus::Approved,
            'created_by' => $userId,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function approve(AssetWriteOff $writeOff, int $userId): AssetWriteOff
    {
        $writeOff->update([
            'status' => AssetWriteOffStatus::Approved,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $writeOff->fresh();
    }

    public function post(AssetWriteOff $writeOff, int $userId): AssetWriteOff
    {
        if ($writeOff->status !== AssetWriteOffStatus::Approved) {
            throw ValidationException::withMessages([
                'writeoff' => __('Write-off must be approved before posting.'),
            ]);
        }

        if ($writeOff->posted_journal_id) {
            return $writeOff;
        }

        $asset = $writeOff->asset;
        $this->periodControl->assertPeriodOpenForPosting($asset->company_id, $writeOff->write_off_date->toDateString());

        return DB::transaction(function () use ($writeOff, $asset, $userId) {
            $journal = $this->posting->postWriteOff($writeOff, $asset, $userId);

            $asset->update([
                'status' => FixedAssetStatus::Disposed,
                'is_fully_depreciated' => true,
                'net_book_value' => 0,
            ]);

            $writeOff->update(['status' => AssetWriteOffStatus::Posted]);

            $this->timeline->record(
                $asset,
                'written_off',
                __('Asset written off'),
                $writeOff->notes,
                $writeOff,
                $userId,
                ['journal_id' => $journal->id],
            );

            return $writeOff->fresh(['asset', 'journal']);
        });
    }
}
