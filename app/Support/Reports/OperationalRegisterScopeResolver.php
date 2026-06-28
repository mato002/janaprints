<?php

namespace App\Support\Reports;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OperationalRegisterScopeResolver
{
    public function __construct(
        protected IntelligenceScopeResolver $scopeResolver,
    ) {}

    /**
     * @return array{
     *     scope: OperationalRegisterScope,
     *     branches: Collection<int, Branch>,
     *     can_export: bool,
     *     filters: array<string, mixed>,
     *     register: string
     * }
     */
    public function resolve(Request $request): array
    {
        $base = $this->scopeResolver->resolve($request, includeCustomer: true, includeVendor: true, defaultBranchFromTenant: false);
        $preset = (string) $request->query('preset', '');
        [$fromDate, $toDate] = $this->resolveDates($preset, $base['filters']);

        $register = (string) $request->query('register', 'daily_sales');
        $validRegisters = array_keys(config('production_operational_registers.registers', []));

        if (! in_array($register, $validRegisters, true)) {
            $register = 'daily_sales';
        }

        $scope = new OperationalRegisterScope(
            companyId: $base['scope']->companyId,
            branchId: $base['scope']->branchId,
            fromDate: $fromDate,
            toDate: $toDate,
            department: $request->filled('department') ? (string) $request->query('department') : null,
            customerId: $base['scope']->customerId,
            machineId: $request->filled('machine_id') ? (int) $request->query('machine_id') : null,
            operatorId: $request->filled('operator_id') ? (int) $request->query('operator_id') : null,
            vendorId: $base['scope']->vendorId,
            paymentStatus: $request->filled('payment_status') ? (string) $request->query('payment_status') : null,
            productionStatus: $request->filled('production_status') ? (string) $request->query('production_status') : null,
            search: trim((string) $request->query('search', '')),
            register: $register,
            preset: $preset,
        );

        return [
            'scope' => $scope,
            'branches' => $base['branches'],
            'can_export' => $base['can_export'],
            'register' => $register,
            'filters' => array_merge($scope->toFilterArray(), [
                'branch_id' => $scope->branchId,
                'preset' => $preset,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: string, 1: string}
     */
    protected function resolveDates(string $preset, array $filters): array
    {
        if ($preset !== '' && array_key_exists($preset, config('production_operational_registers.presets', []))) {
            return match ($preset) {
                'today' => [today()->toDateString(), today()->toDateString()],
                'yesterday' => [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
                'week' => [today()->startOfWeek()->toDateString(), today()->toDateString()],
                'month' => [today()->startOfMonth()->toDateString(), today()->toDateString()],
                'quarter' => [today()->startOfQuarter()->toDateString(), today()->toDateString()],
                'year' => [today()->startOfYear()->toDateString(), today()->toDateString()],
                default => [(string) $filters['from_date'], (string) $filters['to_date']],
            };
        }

        return [(string) $filters['from_date'], (string) $filters['to_date']];
    }
}
