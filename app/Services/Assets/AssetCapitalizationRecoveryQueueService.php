<?php

namespace App\Services\Assets;

use App\Enums\AssetAcquisitionAccountingStatus;
use App\Enums\AssetAcquisitionSource;
use App\Models\Assets\FixedAsset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AssetCapitalizationRecoveryQueueService
{
    public function __construct(
        protected AssetCapitalizationPostingRecoveryService $recovery,
    ) {}

    public function pendingCount(int $companyId, ?int $branchId = null): int
    {
        return $this->recoveryQuery($companyId, $branchId)->count();
    }

    public function paginatedIndex(Request $request, int $companyId, ?int $branchId = null): LengthAwarePaginator
    {
        $query = $this->recoveryQuery($companyId, $branchId)
            ->with([
                'category:id,name',
                'capitalizationCandidate.capitalizer:id,name',
            ]);

        if ($status = AssetAcquisitionAccountingStatus::tryFrom((string) $request->query('status', ''))) {
            $query->where('acquisition_accounting_status', $status);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $like = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $inner) use ($like) {
                $inner->where('asset_number', 'like', $like)
                    ->orWhere('asset_name', 'like', $like);
            });
        }

        return $query
            ->orderByDesc('capitalization_date')
            ->orderByDesc('id')
            ->paginate(config('platform.pagination.default', 15))
            ->withQueryString();
    }

    public function recoveryReason(FixedAsset $asset): string
    {
        return $this->recovery->recoveryReason($asset);
    }

    protected function recoveryQuery(int $companyId, ?int $branchId = null): Builder
    {
        return FixedAsset::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
            ->where('acquisition_source', AssetAcquisitionSource::Procurement)
            ->whereNull('posted_acquisition_journal_id')
            ->whereNull('archived_at');
    }
}
