<?php

namespace App\Services\MasterData;

use App\Models\MasterDataValue;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\MasterData\MasterDataRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MasterDataService
{
    public function __construct(
        protected MasterDataRegistry $registry,
        protected MasterDataDependencyService $dependencies,
    ) {}

    public function paginate(
        ?string $category = null,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = MasterDataValue::query()
            ->forTenant()
            ->with(['creator:id,name', 'company:id,name'])
            ->orderBy('category_key')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($category && $category !== 'all') {
            $query->where('category_key', $category);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search !== null && trim($search) !== '') {
            $like = '%'.trim($search).'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): MasterDataValue
    {
        $this->assertValidCategory($data['category_key']);

        $value = MasterDataValue::query()->create([
            ...$data,
            'created_by' => $actor->getKey(),
            'is_active' => $data['is_active'] ?? true,
        ]);

        ActivityLogger::log('master_data_created', $value);

        return $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(MasterDataValue $value, array $data, ?User $actor = null): MasterDataValue
    {
        if (array_key_exists('is_active', $data) && $data['is_active'] === false && $value->is_active) {
            $check = $this->dependencies->check($value);

            if ($check['blocked']) {
                throw ValidationException::withMessages([
                    'is_active' => $this->dependencyMessage($check['usages']),
                ]);
            }
        }

        $value->update($data);

        ActivityLogger::log('master_data_updated', $value, $actor?->getKey());

        return $value->fresh();
    }

    public function deactivate(MasterDataValue $value, User $actor): MasterDataValue
    {
        $check = $this->dependencies->check($value);

        if ($check['blocked']) {
            throw ValidationException::withMessages([
                'is_active' => $this->dependencyMessage($check['usages']),
            ]);
        }

        $value->update(['is_active' => false]);

        ActivityLogger::log('master_data_deactivated', $value, null, [
            'deactivated_by' => $actor->getKey(),
        ]);

        return $value->fresh();
    }

    public function reactivate(MasterDataValue $value): MasterDataValue
    {
        $value->update(['is_active' => true]);

        ActivityLogger::log('master_data_reactivated', $value);

        return $value->fresh();
    }

    /**
     * @return Collection<int, MasterDataValue>
     */
    public function exportCollection(?string $category = null): Collection
    {
        $query = MasterDataValue::query()
            ->forTenant()
            ->orderBy('category_key')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($category && $category !== 'all') {
            $query->where('category_key', $category);
        }

        return $query->get();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function importRows(array $rows, int $companyId, ?int $branchId, User $actor): int
    {
        $imported = 0;

        foreach ($rows as $row) {
            if (! $this->registry->isValidCategory($row['category_key'])) {
                continue;
            }

            MasterDataValue::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'category_key' => $row['category_key'],
                    'code' => $row['code'],
                ],
                [
                    'branch_id' => $branchId,
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'created_by' => $actor->getKey(),
                ],
            );

            $imported++;
        }

        ActivityLogger::log('master_data_imported', null, $actor->getKey(), [
            'rows' => $imported,
            'company_id' => $companyId,
        ]);

        return $imported;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function activeOptions(string $categoryKey, int $companyId): array
    {
        return MasterDataValue::query()
            ->where('company_id', $companyId)
            ->where('category_key', $categoryKey)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (MasterDataValue $value) => [
                'value' => $value->code,
                'label' => $value->name,
            ])
            ->all();
    }

    protected function assertValidCategory(string $categoryKey): void
    {
        if (! $this->registry->isValidCategory($categoryKey)) {
            throw ValidationException::withMessages([
                'category_key' => __('Invalid master data category.'),
            ]);
        }
    }

    /**
     * @param  list<array{module: string, count: int}>  $usages
     */
    protected function dependencyMessage(array $usages): string
    {
        $parts = collect($usages)
            ->map(fn (array $usage) => "{$usage['module']} ({$usage['count']})")
            ->implode(', ');

        return __('Cannot deactivate: value is in use by :modules.', ['modules' => $parts]);
    }
}
