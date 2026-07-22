<?php

namespace App\Services\Assets;

use App\Enums\ProductionMachineStatus;
use App\Models\Assets\MachineProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class MachineIndexService
{
    public function __construct(
        protected MachineCapacityService $capacity,
        protected MachineAvailabilityService $availability,
        protected MachineDashboardService $dashboard,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $profiles = $this->paginatedIndex($request);
        $metrics = $this->capacity->metricsForProfiles($profiles->getCollection());

        $profiles->getCollection()->transform(function (MachineProfile $profile) use ($metrics) {
            $profile->setAttribute('capacity_metrics', $metrics[$profile->id] ?? []);
            $profile->setAttribute('availability', $this->availability->evaluate($profile));

            return $profile;
        });

        return [
            'machines' => $profiles,
            'filters' => $this->filtersFromRequest($request),
            'statuses' => ProductionMachineStatus::cases(),
            'summary' => $this->dashboard->summaryStrip(
                (int) tenant()->companyId(),
                tenant()->branchId(),
            ),
        ];
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        $filters = $this->filtersFromRequest($request);

        $query = MachineProfile::query()
            ->forTenant()
            ->with([
                'asset:id,public_id,asset_name,asset_number,branch_id,status',
                'asset.branch:id,name',
                'workCenter:id,name,code,fixed_asset_id',
            ]);

        if (filled($filters['search'] ?? null)) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('machine_code', 'like', "%{$search}%")
                    ->orWhere('machine_type', 'like', "%{$search}%")
                    ->orWhereHas('asset', fn ($aq) => $aq
                        ->where('asset_name', 'like', "%{$search}%")
                        ->orWhere('asset_number', 'like', "%{$search}%"));
            });
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('production_status', $filters['status']);
        }

        if (filled($filters['machine_type'] ?? null)) {
            $query->where('machine_type', $filters['machine_type']);
        }

        return $query
            ->orderBy('machine_code')
            ->paginate(config('platform.pagination.default', 15))
            ->withQueryString();
    }

    /**
     * @return array{search: ?string, status: ?string, machine_type: ?string}
     */
    protected function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'machine_type' => $request->query('machine_type'),
        ];
    }
}
