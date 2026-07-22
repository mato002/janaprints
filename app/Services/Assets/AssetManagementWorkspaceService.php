<?php

namespace App\Services\Assets;

use App\Models\Assets\AssetCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

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
                'categories' => $this->categories($request, $user),
                'can_manage_categories' => $user?->can('create', AssetCategory::class) ?? false,
                'can_view_categories' => $user?->can('viewAny', AssetCategory::class) ?? false,
            ],
        );
    }

    /**
     * @return LengthAwarePaginator<int, AssetCategory>
     */
    protected function categories(Request $request, ?User $user): LengthAwarePaginator
    {
        if (! $user?->can('viewAny', AssetCategory::class)) {
            return AssetCategory::query()->whereRaw('0 = 1')->paginate(
                config('platform.pagination.table', 10),
                ['*'],
                'category_page',
            );
        }

        return AssetCategory::query()
            ->forTenant()
            ->notArchived()
            ->withCount('assets')
            ->orderBy('name')
            ->paginate(
                config('platform.pagination.table', 10),
                ['*'],
                'category_page',
            )
            ->withQueryString();
    }
}
