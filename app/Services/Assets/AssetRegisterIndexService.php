<?php

namespace App\Services\Assets;

use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssetRegisterIndexService
{
    public function build(Request $request, ?User $user = null): array
    {
        $user ??= auth()->user();

        return [
            'assets' => $this->paginatedIndex($request),
            'filters' => $this->filtersFromRequest($request),
            'filter_options' => $this->filterOptions(),
            'has_active_filters' => $this->hasActiveFilters($request),
            'bulk_actions' => $this->bulkActions($user),
            'can_create' => $user?->can('create', FixedAsset::class) ?? false,
            'can_export' => $user?->can('viewAny', FixedAsset::class) ?? false,
        ];
    }

    public function paginatedIndex(Request $request): LengthAwarePaginator
    {
        $query = FixedAsset::query()
            ->forTenant()
            ->forBranchContext()
            ->with([
                'category:id,name,asset_type',
                'branch:id,name',
                'assignedUser:id,name',
            ]);

        if (! $request->boolean('include_archived')) {
            $query->notArchived();
        }

        $this->applyFilters($query, $request);
        $this->applySort($query, $request);

        return $query->paginate(config('platform.pagination.admin', 20))->withQueryString();
    }

    /**
     * @return Collection<int, FixedAsset>
     */
    public function exportQuery(Request $request): Collection
    {
        $query = FixedAsset::query()
            ->forTenant()
            ->forBranchContext()
            ->with(['category:id,name', 'branch:id,name', 'assignedUser:id,name'])
            ->notArchived();

        $this->applyFilters($query, $request);
        $this->applySort($query, $request);

        return $query->limit(5000)->get();
    }

    public function filterOptions(): array
    {
        $companyId = tenant()->companyId();

        return [
            'categories' => AssetCategory::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->active()
                ->orderBy('name')
                ->get(['id', 'name']),
            'branches' => Branch::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'users' => User::query()
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'statuses' => FixedAssetStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'category_id' => $request->query('category_id'),
            'status' => $request->query('status'),
            'branch_id' => $request->query('branch_id'),
            'assigned_to_user_id' => $request->query('assigned_to_user_id'),
            'sort' => $request->query('sort', 'newest'),
            'include_archived' => $request->boolean('include_archived'),
        ];
    }

    public function hasActiveFilters(Request $request): bool
    {
        $filters = $this->filtersFromRequest($request);

        return ($filters['search'] ?? '') !== ''
            || ! empty($filters['category_id'])
            || ! empty($filters['status'])
            || ! empty($filters['branch_id'])
            || ! empty($filters['assigned_to_user_id'])
            || ($filters['include_archived'] ?? false);
    }

    protected function applyFilters(Builder $query, Request $request): void
    {
        $search = $request->string('search')->trim()->toString();

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('asset_number', 'like', "%{$search}%")
                    ->orWhere('asset_name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('manufacturer', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('asset_category_id', $categoryId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($branchId = $request->query('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($userId = $request->query('assigned_to_user_id')) {
            $query->where('assigned_to_user_id', $userId);
        }
    }

    protected function applySort(Builder $query, Request $request): void
    {
        match ($request->query('sort', 'newest')) {
            'oldest' => $query->orderBy('created_at'),
            'cost_high' => $query->orderByDesc('acquisition_cost'),
            'cost_low' => $query->orderBy('acquisition_cost'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    protected function bulkActions(?User $user): array
    {
        if (! $user?->can('assets.manage')) {
            return [];
        }

        return [
            ['key' => 'assign', 'label' => __('Assign')],
            ['key' => 'change_status', 'label' => __('Change Status')],
            ['key' => 'archive', 'label' => __('Archive')],
        ];
    }
}
