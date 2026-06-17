<?php

namespace App\Support\Hr;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRosterQuery
{
    /**
     * Resolve the company scope used across HR employee lists (People, Payroll, email).
     */
    public static function resolveCompanyId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        return tenant()->companyId() ?? $user?->company_id;
    }

    /**
     * @param  array{
     *     active?: bool|null,
     *     branch_id?: int|null,
     * }  $filters
     *     active: true = active only (default), false = inactive only, null = all statuses
     */
    public static function query(?int $companyId = null, array $filters = []): Builder
    {
        $companyId ??= self::resolveCompanyId();

        if ($companyId === null) {
            return Employee::query()->whereRaw('1 = 0');
        }

        $query = Employee::query()->where('company_id', $companyId);

        $active = $filters['active'] ?? true;

        if ($active === true) {
            $query->where('is_active', true);
        } elseif ($active === false) {
            $query->where('is_active', false);
        }

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', (int) $filters['branch_id']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $with
     */
    public static function paginate(array $filters = [], int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        $query = self::query(null, $filters)->orderBy('employee_number');

        if ($with !== []) {
            $query->with($with);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return array{active: bool|null, branch_id: int|null}
     */
    public static function filtersFromRequest(\Illuminate\Http\Request $request): array
    {
        return [
            'active' => match ($request->query('status')) {
                'all' => null,
                'inactive' => false,
                default => true,
            },
            'branch_id' => $request->filled('branch_id') ? $request->integer('branch_id') : null,
        ];
    }
}
