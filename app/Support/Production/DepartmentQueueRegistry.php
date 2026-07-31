<?php

namespace App\Support\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\WorkCenter;
use Illuminate\Database\Eloquent\Builder;

class DepartmentQueueRegistry
{
    /**
     * @return list<array{slug: string, label: string, url: string, active: bool, count: ?int}>
     */
    public function navigation(?string $activeSlug = null): array
    {
        $items = [
            [
                'slug' => '',
                'label' => __('All departments'),
                'url' => ProductionFloorDeskViews::queueIndexUrl(),
                'active' => $activeSlug === null || $activeSlug === '',
                'count' => null,
            ],
        ];

        foreach ($this->availableDepartments() as $slug => $department) {
            $items[] = [
                'slug' => $slug,
                'label' => $department['label'],
                'url' => ProductionFloorDeskViews::queueIndexUrl($slug),
                'active' => $activeSlug === $slug,
                'count' => null,
            ];
        }

        return $items;
    }

    /**
     * @return array<string, array{label: string, work_center_codes: list<string>, production_types: list<string>, job_statuses: list<string>}>
     */
    public function availableDepartments(): array
    {
        $configured = config('production.departments', []);
        $activeCodes = WorkCenter::query()
            ->forTenant()
            ->where('is_active', true)
            ->pluck('code')
            ->map(fn ($code) => strtoupper((string) $code))
            ->all();

        $available = [];

        foreach ($configured as $slug => $department) {
            $codes = array_map('strtoupper', $department['work_center_codes'] ?? []);
            $jobStatuses = $department['job_statuses'] ?? [];

            if ($jobStatuses !== []) {
                $available[$slug] = [
                    'label' => __($department['label']),
                    'work_center_codes' => [],
                    'production_types' => $department['production_types'] ?? [],
                    'job_statuses' => $jobStatuses,
                ];

                continue;
            }

            if ($codes === [] || array_intersect($codes, $activeCodes) !== []) {
                $available[$slug] = [
                    'label' => __($department['label']),
                    'work_center_codes' => $codes,
                    'production_types' => $department['production_types'] ?? [],
                    'job_statuses' => [],
                ];
            }
        }

        $focus = config('production.focus_department_slugs', []);
        if ($focus !== []) {
            $available = array_intersect_key($available, array_flip($focus));
        }

        return $available;
    }

    public function isValidSlug(string $slug): bool
    {
        return array_key_exists($slug, $this->availableDepartments());
    }

    /**
     * @return array{label: string, work_center_codes: list<string>, production_types: list<string>, job_statuses: list<string>}|null
     */
    public function department(string $slug): ?array
    {
        return $this->availableDepartments()[$slug] ?? null;
    }

    public function applyDepartmentScope(Builder $query, string $slug): Builder
    {
        $department = $this->department($slug);

        if (! $department) {
            return $query->whereRaw('0 = 1');
        }

        if ($department['job_statuses'] !== []) {
            return $query->whereHas('jobCard', function (Builder $jobQuery) use ($department) {
                $jobQuery->whereIn('status', $department['job_statuses']);
            });
        }

        $codes = $department['work_center_codes'];
        $types = $department['production_types'];

        return $query->where(function (Builder $scope) use ($codes, $types) {
            if ($codes !== []) {
                $scope->whereHas('workCenter', fn (Builder $wc) => $wc->whereIn('code', $codes));
            }

            if ($types !== []) {
                $scope->orWhereHas('jobCard', fn (Builder $job) => $job->whereIn('production_type', $types));
            }
        });
    }
}
