<?php

namespace App\Services\Assets;

use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssetManagementWorkspaceService
{
    public function __construct(
        protected AssetRegisterIndexService $register,
        protected AssetDashboardService $dashboard,
    ) {}

    public function build(Request $request, ?User $user = null): array
    {
        $user ??= auth()->user();
        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        return array_merge(
            $this->register->build($request, $user),
            [
                'stats' => $this->dashboard->build($companyId, $branchId),
                'categories' => $this->categories($user),
                'can_manage_categories' => $user?->can('create', AssetCategory::class) ?? false,
                'can_view_categories' => $user?->can('viewAny', AssetCategory::class) ?? false,
            ],
        );
    }

    /**
     * @return Collection<int, AssetCategory>
     */
    protected function categories(?User $user): Collection
    {
        if (! $user?->can('viewAny', AssetCategory::class)) {
            return collect();
        }

        return AssetCategory::query()
            ->forTenant()
            ->notArchived()
            ->withCount('assets')
            ->orderBy('name')
            ->get();
    }
}
