<?php

namespace App\Services\PrintingIntelligence;

use Illuminate\Database\Eloquent\Builder;

class PrintingIntelligenceScope
{
    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, mixed>  $filters
     */
    public static function applyBranchScope(Builder $query, array $filters, string $column = 'branch_id'): Builder
    {
        if (! empty($filters['branch_id'])) {
            $query->where($column, (int) $filters['branch_id']);
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\PrintingIntelligence\PrintProfitabilitySnapshot>  $rows
     * @param  callable(\App\Models\PrintingIntelligence\PrintProfitabilitySnapshot): int|string|null  $groupKey
     * @return \Illuminate\Support\Collection<int, \App\Models\PrintingIntelligence\PrintProfitabilitySnapshot>
     */
    public static function dedupeSnapshotsByLatestDate($rows, callable $groupKey)
    {
        return $rows
            ->groupBy(fn ($row) => (string) $groupKey($row))
            ->map(fn ($group) => $group->sortByDesc('snapshot_date')->first())
            ->values();
    }
}
