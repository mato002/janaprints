<?php

namespace App\Services\Assets;

use App\Enums\DepreciationPostingStatus;
use App\Enums\DepreciationRunStatus;
use App\Enums\DocumentType;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\DepreciationRun;
use App\Models\Assets\FixedAsset;
use App\Support\Accounting\AssetAccountingPostingService;
use App\Support\Platform\NumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepreciationRunService
{
    public function __construct(
        protected DepreciationCalculationService $calculator,
        protected AssetPeriodControlService $periodControl,
        protected AssetFinanceTimelineService $timeline,
        protected AssetAccountingPostingService $posting,
    ) {}

    public function createDraft(int $companyId, string $period, int $userId, ?int $branchId = null, bool $dryRun = false): DepreciationRun
    {
        $this->periodControl->assertRunAllowed($companyId, $period);
        $bounds = $this->periodControl->periodBounds($period);

        $existing = DepreciationRun::query()
            ->where('company_id', $companyId)
            ->where('period', $period)
            ->where('status', DepreciationRunStatus::Draft)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DepreciationRun::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'run_number' => app(NumberGenerator::class)->generate(DocumentType::DepreciationRun, $companyId),
            'period' => $period,
            'start_date' => $bounds['start_date'],
            'end_date' => $bounds['end_date'],
            'run_date' => now()->toDateString(),
            'status' => DepreciationRunStatus::Draft,
            'executed_by' => $userId,
            'is_dry_run' => $dryRun,
        ]);
    }

    /**
     * @return array{run: DepreciationRun, preview: array<int, array<string, mixed>>}
     */
    public function preview(DepreciationRun $run, ?int $branchId = null): array
    {
        $preview = [];
        $total = 0.0;
        $count = 0;

        $this->eligibleAssetsQuery($run->company_id, $branchId)->each(function (FixedAsset $asset) use ($run, &$preview, &$total, &$count) {
            if ($this->calculator->hasEntryForPeriod($asset, $run->end_date->toDateString())) {
                return;
            }

            $calc = $this->calculator->calculateForPeriod($asset, $run->end_date->toDateString());

            if ($calc['depreciation_amount'] <= 0) {
                return;
            }

            $preview[] = [
                'asset_id' => $asset->id,
                'asset_number' => $asset->asset_number,
                'asset_name' => $asset->asset_name,
                'depreciation_amount' => $calc['depreciation_amount'],
                'accumulated_after' => $calc['accumulated_after'],
                'net_book_value_after' => $calc['net_book_value_after'],
            ];

            $total += $calc['depreciation_amount'];
            $count++;
        });

        $run->update([
            'preview_summary' => [
                'assets' => $preview,
                'total_depreciation' => round($total, 2),
                'assets_count' => $count,
            ],
            'total_depreciation' => round($total, 2),
            'assets_processed' => $count,
        ]);

        return ['run' => $run->fresh(), 'preview' => $preview];
    }

    public function execute(DepreciationRun $run, int $userId, bool $postJournals = true): DepreciationRun
    {
        if ($run->status === DepreciationRunStatus::Completed) {
            throw ValidationException::withMessages([
                'run' => __('This depreciation run is already completed.'),
            ]);
        }

        if ($run->is_dry_run) {
            throw ValidationException::withMessages([
                'run' => __('Dry-run depreciation runs cannot be executed.'),
            ]);
        }

        $this->periodControl->assertPeriodOpenForPosting($run->company_id, $run->end_date->toDateString());

        return DB::transaction(function () use ($run, $userId, $postJournals) {
            $run->update(['status' => DepreciationRunStatus::Running]);

            $total = 0.0;
            $count = 0;

            $this->eligibleAssetsQuery($run->company_id, $run->branch_id)->each(function (FixedAsset $asset) use ($run, $userId, $postJournals, &$total, &$count) {
                if ($this->calculator->hasEntryForPeriod($asset, $run->end_date->toDateString())) {
                    return;
                }

                $calc = $this->calculator->calculateForPeriod($asset, $run->end_date->toDateString());

                if ($calc['depreciation_amount'] <= 0) {
                    return;
                }

                $entry = AssetDepreciationEntry::query()->create([
                    'fixed_asset_id' => $asset->id,
                    'depreciation_run_id' => $run->id,
                    'period_date' => $this->calculator->normalizePeriod($run->end_date->toDateString()),
                    'depreciation_amount' => $calc['depreciation_amount'],
                    'accumulated_after' => $calc['accumulated_after'],
                    'net_book_value_after' => $calc['net_book_value_after'],
                    'posting_status' => DepreciationPostingStatus::Draft,
                ]);

                $asset->update([
                    'accumulated_depreciation' => $calc['accumulated_after'],
                    'net_book_value' => $calc['net_book_value_after'],
                    'last_depreciation_date' => $run->end_date,
                    'is_fully_depreciated' => $calc['is_fully_depreciated'],
                ]);

                if ($postJournals) {
                    $this->posting->postDepreciation($entry->fresh(), $asset->fresh(), $userId);
                    $entry->update([
                        'posting_status' => DepreciationPostingStatus::Posted,
                        'posted_at' => now(),
                        'is_locked' => true,
                    ]);
                }

                $this->timeline->record(
                    $asset,
                    'depreciated',
                    __('Depreciation for :period', ['period' => $run->period]),
                    null,
                    $entry,
                    $userId,
                    ['amount' => $calc['depreciation_amount']],
                );

                $total += $calc['depreciation_amount'];
                $count++;
            });

            $run->update([
                'status' => DepreciationRunStatus::Completed,
                'total_depreciation' => round($total, 2),
                'assets_processed' => $count,
                'executed_by' => $userId,
                'run_date' => now()->toDateString(),
            ]);

            return $run->fresh(['entries.asset']);
        });
    }

    public function cancel(DepreciationRun $run): DepreciationRun
    {
        if ($run->status === DepreciationRunStatus::Completed) {
            throw ValidationException::withMessages([
                'run' => __('Completed depreciation runs cannot be cancelled.'),
            ]);
        }

        $run->update(['status' => DepreciationRunStatus::Cancelled]);

        return $run->fresh();
    }

    protected function eligibleAssetsQuery(int $companyId, ?int $branchId)
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->where('status', FixedAssetStatus::Active)
            ->where('is_fully_depreciated', false)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('category');
    }
}
