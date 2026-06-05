<?php

namespace App\Services\MasterData;

use App\Models\MasterDataValue;
use App\Support\MasterData\MasterDataRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterDataDependencyService
{
    public function __construct(
        protected MasterDataRegistry $registry,
    ) {}

    /**
     * @return array{blocked: bool, usages: list<array{module: string, count: int}>}
     */
    public function check(MasterDataValue $value): array
    {
        $usages = [];

        foreach ($this->registry->dependenciesFor($value->category_key) as $rule) {
            $count = $this->countUsage($value, $rule);

            if ($count > 0) {
                $usages[] = [
                    'module' => $rule['label'] ?? $rule['table'],
                    'count' => $count,
                ];
            }
        }

        return [
            'blocked' => $usages !== [],
            'usages' => $usages,
        ];
    }

    /**
     * @param  array<string, string>  $rule
     */
    protected function countUsage(MasterDataValue $value, array $rule): int
    {
        $table = $rule['table'] ?? null;
        $column = $rule['column'] ?? null;
        $match = $rule['match'] ?? 'code';

        if (! $table || ! $column || ! Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table)->where($column, $this->matchValue($value, $match));

        if (Schema::hasColumn($table, 'company_id')) {
            $query->where('company_id', $value->company_id);
        }

        if ($value->branch_id && Schema::hasColumn($table, 'branch_id')) {
            $query->where('branch_id', $value->branch_id);
        }

        return (int) $query->count();
    }

    protected function matchValue(MasterDataValue $value, string $match): mixed
    {
        return match ($match) {
            'name' => $value->name,
            'lead_source_code' => $this->resolveLeadSourceId($value),
            'uom_code' => $this->resolveUnitOfMeasureId($value),
            default => $value->code,
        };
    }

    protected function resolveLeadSourceId(MasterDataValue $value): ?int
    {
        if (! Schema::hasTable('lead_sources')) {
            return null;
        }

        return DB::table('lead_sources')
            ->where('company_id', $value->company_id)
            ->where('code', $value->code)
            ->value('id');
    }

    protected function resolveUnitOfMeasureId(MasterDataValue $value): ?int
    {
        if (! Schema::hasTable('units_of_measure')) {
            return null;
        }

        return DB::table('units_of_measure')
            ->where('company_id', $value->company_id)
            ->where('code', $value->code)
            ->value('id');
    }
}
