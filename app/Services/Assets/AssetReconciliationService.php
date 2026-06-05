<?php

namespace App\Services\Assets;

use App\Enums\AssetReconciliationStatus;
use App\Enums\DepreciationPostingStatus;
use App\Enums\DocumentType;
use App\Enums\FixedAssetStatus;
use App\Models\Accounting\GlAccount;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\AssetRegisterReconciliation;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\NumberGenerator;

class AssetReconciliationService
{
    public function __construct(
        protected DepreciationCalculationService $calculator,
    ) {}

    public function run(int $companyId, int $userId, ?string $date = null): AssetRegisterReconciliation
    {
        $date ??= now()->toDateString();

        $register = FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->where('status', '!=', FixedAssetStatus::Disposed->value)
            ->get();

        $registerCost = round($register->sum('acquisition_cost'), 2);
        $registerAccumulated = round($register->sum('accumulated_depreciation'), 2);
        $registerNbv = round($register->sum(fn ($a) => $a->netBookValue()), 2);

        $glTotals = $this->glTotals($companyId);
        $varianceCost = round($registerCost - $glTotals['cost'], 2);
        $varianceNbv = round($registerNbv - $glTotals['nbv'], 2);

        $findings = $this->detectFindings($companyId, $register, $varianceCost, $varianceNbv);

        $status = abs($varianceCost) < 0.01 && abs($varianceNbv) < 0.01
            ? AssetReconciliationStatus::Reconciled
            : AssetReconciliationStatus::VarianceDetected;

        return AssetRegisterReconciliation::query()->create([
            'company_id' => $companyId,
            'reconciliation_no' => app(NumberGenerator::class)->generate(DocumentType::AssetReconciliation, $companyId),
            'reconciliation_date' => $date,
            'register_cost' => $registerCost,
            'register_accumulated' => $registerAccumulated,
            'register_nbv' => $registerNbv,
            'gl_cost' => $glTotals['cost'],
            'gl_accumulated' => $glTotals['accumulated'],
            'gl_nbv' => $glTotals['nbv'],
            'variance_cost' => $varianceCost,
            'variance_nbv' => $varianceNbv,
            'status' => $status,
            'findings' => $findings,
            'reconciled_by' => $userId,
        ]);
    }

    /**
     * @return array{cost: float, accumulated: float, nbv: float}
     */
    protected function glTotals(int $companyId): array
    {
        $fixedAssetCodes = config('posting_account_keys.fixed_asset.default_code', '1530');
        $accumulatedCode = config('posting_account_keys.accumulated_depreciation.default_code', '1550');

        $cost = $this->accountBalance($companyId, $fixedAssetCodes);
        $accumulated = abs($this->accountBalance($companyId, $accumulatedCode));

        return [
            'cost' => round($cost, 2),
            'accumulated' => round($accumulated, 2),
            'nbv' => round(max(0, $cost - $accumulated), 2),
        ];
    }

    protected function accountBalance(int $companyId, string $code): float
    {
        $account = GlAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->first();

        return $account ? (float) $account->current_balance : 0.0;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FixedAsset>  $register
     * @return list<array<string, mixed>>
     */
    protected function detectFindings(int $companyId, $register, float $varianceCost, float $varianceNbv): array
    {
        $findings = [];

        if (abs($varianceCost) >= 0.01) {
            $findings[] = [
                'type' => 'gl_cost_difference',
                'message' => __('Asset register cost differs from GL fixed asset balance.'),
                'variance' => $varianceCost,
            ];
        }

        if (abs($varianceNbv) >= 0.01) {
            $findings[] = [
                'type' => 'nbv_difference',
                'message' => __('Net book value differs between register and GL.'),
                'variance' => $varianceNbv,
            ];
        }

        $unposted = AssetDepreciationEntry::query()
            ->where('posting_status', DepreciationPostingStatus::Draft)
            ->whereHas('asset', fn ($q) => $q->where('company_id', $companyId))
            ->count();

        if ($unposted > 0) {
            $findings[] = [
                'type' => 'unposted_depreciation',
                'message' => __(':count depreciation entries are not posted.', ['count' => $unposted]),
                'count' => $unposted,
            ];
        }

        $missingGl = $register->filter(fn (FixedAsset $a) => ! $a->category?->default_gl_code)->count();
        if ($missingGl > 0) {
            $findings[] = [
                'type' => 'missing_gl_mapping',
                'message' => __(':count assets lack category GL mapping.', ['count' => $missingGl]),
                'count' => $missingGl,
            ];
        }

        return $findings;
    }
}
