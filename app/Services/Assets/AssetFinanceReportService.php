<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\FixedAsset;
use Illuminate\Support\Collection;

class AssetFinanceReportService
{
    public function __construct(
        protected DepreciationCalculationService $calculator,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, FixedAsset>
     */
    public function registerReport(int $companyId, array $filters = []): Collection
    {
        return $this->baseQuery($companyId, $filters)
            ->with(['category', 'branch'])
            ->orderBy('asset_number')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function valuationReport(int $companyId, array $filters = []): Collection
    {
        return $this->registerReport($companyId, $filters)->map(function (FixedAsset $asset) {
            $profile = $this->calculator->financialProfile($asset);

            return [
                'asset' => $asset,
                'profile' => $profile,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function depreciationSchedule(int $companyId, array $filters = []): Collection
    {
        return AssetDepreciationEntry::query()
            ->whereHas('asset', function ($q) use ($companyId, $filters) {
                $q->where('company_id', $companyId);
                if (! empty($filters['branch_id'])) {
                    $q->where('branch_id', $filters['branch_id']);
                }
            })
            ->with(['asset:id,asset_number,asset_name,branch_id', 'journal:id,reference'])
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('period_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('period_date', '<=', $to))
            ->orderByDesc('period_date')
            ->get();
    }

    public function fullyDepreciated(int $companyId, ?int $branchId = null): Collection
    {
        return $this->baseQuery($companyId, ['branch_id' => $branchId])
            ->where('is_fully_depreciated', true)
            ->with(['category', 'branch'])
            ->get();
    }

    public function nearEndOfLife(int $companyId, ?int $branchId = null, int $monthsThreshold = 6): Collection
    {
        return $this->baseQuery($companyId, ['branch_id' => $branchId])
            ->where('is_fully_depreciated', false)
            ->with(['category', 'branch'])
            ->get()
            ->filter(function (FixedAsset $asset) use ($monthsThreshold) {
                return $this->calculator->financialProfile($asset)['remaining_months'] <= $monthsThreshold;
            })
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function baseQuery(int $companyId, array $filters = [])
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->when($filters['branch_id'] ?? null, fn ($q, $id) => $q->where('branch_id', $id))
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('asset_category_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status), fn ($q) => $q->where('status', '!=', FixedAssetStatus::Disposed->value));
    }
}
