<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Collection;

class EmployeeRoleReconciliationService
{
    public function __construct(
        protected EmployeeActivationRoleResolver $roleResolver,
    ) {}

    /**
     * @return list<array{employee: Employee, status: string, expected: ?string, assigned: list<string>, message: string}>
     */
    public function audit(?int $companyId = null): array
    {
        return $this->employees($companyId)
            ->map(fn (Employee $employee) => $this->inspect($employee))
            ->all();
    }

    /**
     * @return array{checked: int, fixed: int, ok: int, skipped: int, results: list<array<string, mixed>>}
     */
    public function reconcile(?int $companyId = null, bool $dryRun = false): array
    {
        $summary = ['checked' => 0, 'fixed' => 0, 'ok' => 0, 'skipped' => 0, 'results' => []];

        foreach ($this->employees($companyId) as $employee) {
            $summary['checked']++;
            $result = $this->inspect($employee);

            if ($result['status'] === 'ok') {
                $summary['ok']++;

                continue;
            }

            if ($result['status'] === 'skipped') {
                $summary['skipped']++;
                $summary['results'][] = $result;

                continue;
            }

            if (! $dryRun && $result['expected'] && $employee->user) {
                if (! filled($employee->activation_role)) {
                    $employee->update(['activation_role' => $result['expected']]);
                }

                if ($result['status'] === 'mismatch') {
                    $employee->user->syncRoles([$result['expected']]);
                    $result['status'] = 'fixed';
                    $result['assigned'] = [$result['expected']];
                    $result['message'] = __('Role updated to :role.', ['role' => $result['expected']]);
                    $summary['fixed']++;
                }
            }

            $summary['results'][] = $result;
        }

        return $summary;
    }

    public function expectedRole(Employee $employee): ?string
    {
        $employee->loadMissing(['user.roles', 'jobTitle', 'department', 'activations']);

        if (filled($employee->activation_role)) {
            return $this->roleResolver->resolve($employee, $employee->activation_role);
        }

        $openIntendedRole = $employee->activations
            ->sortByDesc(fn ($activation) => $activation->id)
            ->first(fn ($activation) => filled($activation->intended_role))
            ?->intended_role;

        if ($openIntendedRole) {
            return $this->roleResolver->resolve($employee, $openIntendedRole);
        }

        $assignedRole = $employee->user?->roles->first()?->name;

        if (filled($assignedRole) && $this->roleResolver->roleExists($assignedRole)) {
            return $assignedRole;
        }

        return $this->roleResolver->resolve($employee);
    }

    /**
     * @return array{employee_number: string, name: string, email: ?string, status: string, expected: ?string, assigned: list<string>, message: string}
     */
    public function inspect(Employee $employee): array
    {
        $employee->loadMissing(['user.roles']);

        $assigned = $employee->user?->roles->pluck('name')->all() ?? [];
        $expected = $this->expectedRole($employee);

        if (! $employee->user) {
            return $this->result($employee, 'skipped', $expected, $assigned, __('No linked user account.'));
        }

        if (! $expected) {
            return $this->result($employee, 'skipped', $expected, $assigned, __('No resolvable ERP role.'));
        }

        if ($assigned === [$expected]) {
            return $this->result($employee, 'ok', $expected, $assigned, __('Role matches.'));
        }

        if ($assigned === []) {
            return $this->result(
                $employee,
                'mismatch',
                $expected,
                $assigned,
                __('User has no role; expected :role.', ['role' => $expected]),
            );
        }

        return $this->result(
            $employee,
            'mismatch',
            $expected,
            $assigned,
            __('Assigned :assigned; expected :expected.', [
                'assigned' => implode(', ', $assigned),
                'expected' => $expected,
            ]),
        );
    }

    public function syncUserRole(User $user, Employee $employee): ?string
    {
        $expected = $this->expectedRole($employee);

        if (! $expected) {
            return null;
        }

        if ($user->roles->pluck('name')->all() !== [$expected]) {
            $user->syncRoles([$expected]);
        }

        return $expected;
    }

    /**
     * @return Collection<Employee>
     */
    protected function employees(?int $companyId): Collection
    {
        return Employee::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->with(['user.roles', 'jobTitle', 'department', 'activations'])
            ->whereHas('user')
            ->orderBy('employee_number')
            ->get();
    }

    /**
     * @param  list<string>  $assigned
     * @return array{employee_number: string, name: string, email: ?string, status: string, expected: ?string, assigned: list<string>, message: string}
     */
    protected function result(Employee $employee, string $status, ?string $expected, array $assigned, string $message): array
    {
        return [
            'employee_number' => $employee->employee_number,
            'name' => $employee->full_name,
            'email' => $employee->email,
            'status' => $status,
            'expected' => $expected,
            'assigned' => $assigned,
            'message' => $message,
        ];
    }
}
