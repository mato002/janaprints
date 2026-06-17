<?php

namespace App\Support\Hr;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeLifecycleService
{
    public const PROTECTED_EMPLOYEE_NUMBERS = ['EMP-0001'];

    /**
     * Permanently remove an employee and linked login account(s).
     *
     * @return array{employee_number: string, user_emails: list<string>}
     */
    public function purge(Employee $employee): array
    {
        $this->assertCanPurge($employee);

        $employeeNumber = $employee->employee_number;
        $email = $employee->email;
        $deletedUserEmails = [];

        DB::transaction(function () use ($employee, $email, &$deletedUserEmails) {
            $linkedUser = $employee->user;

            if ($linkedUser !== null) {
                $this->deleteUserAccount($linkedUser);
                $deletedUserEmails[] = $linkedUser->email;
            }

            $employee->delete();

            if (filled($email)) {
                User::query()
                    ->where('email', $email)
                    ->whereNull('employee_id')
                    ->where('id', '!=', 1)
                    ->get()
                    ->each(function (User $orphan) use (&$deletedUserEmails) {
                        if (! $this->isDemoOrTestStaffUser($orphan)) {
                            return;
                        }

                        $this->deleteUserAccount($orphan);
                        $deletedUserEmails[] = $orphan->email;
                    });
            }
        });

        return [
            'employee_number' => $employeeNumber,
            'user_emails' => array_values(array_unique($deletedUserEmails)),
        ];
    }

    /**
     * @return array{employees: list<Employee>, count: int}
     */
    public function findDemoAndInactiveEmployees(?int $companyId = null): array
    {
        $employees = $this->demoEmployeeQuery(
            Employee::query()->with('user')->orderBy('id'),
            $companyId,
        )->get();

        return [
            'employees' => $employees->all(),
            'count' => $employees->count(),
        ];
    }

    /**
     * @return list<array{employee_number: string, user_emails: list<string>}>
     */
    public function purgeDemoAndInactiveEmployees(?int $companyId = null): array
    {
        $results = [];

        foreach ($this->findDemoAndInactiveEmployees($companyId)['employees'] as $employee) {
            $results[] = $this->purge($employee);
        }

        $this->purgeOrphanDemoStaffUsers($companyId);

        return $results;
    }

    public function purgeOrphanDemoStaffUsers(?int $companyId = null): int
    {
        $deleted = 0;

        $this->orphanDemoStaffUserQuery($companyId)
            ->get()
            ->each(function (User $user) use (&$deleted) {
                $this->deleteUserAccount($user);
                $deleted++;
            });

        return $deleted;
    }

    protected function demoEmployeeQuery(Builder $query, ?int $companyId = null): Builder
    {
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        return $query
            ->where('id', '!=', 1)
            ->whereNotIn('employee_number', self::PROTECTED_EMPLOYEE_NUMBERS)
            ->where(function (Builder $inner) {
                $inner->where('is_active', false)
                    ->orWhere('email', 'like', '%@example.com')
                    ->orWhere(function (Builder $local) {
                        $local->where('email', 'like', '%@janaprints.local')
                            ->whereNotIn('employee_number', self::PROTECTED_EMPLOYEE_NUMBERS);
                    });
            });
    }

    protected function orphanDemoStaffUserQuery(?int $companyId = null): Builder
    {
        $query = User::query()
            ->whereNull('employee_id')
            ->whereNull('customer_id')
            ->where('id', '!=', 1)
            ->where(function (Builder $inner) {
                $inner->where(function (Builder $inactiveLocal) {
                    $inactiveLocal->where('is_active', false)
                        ->where('email', 'like', '%@janaprints.local');
                })->orWhere('email', 'like', '%@example.com');
            });

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }

    protected function deleteUserAccount(User $user): void
    {
        if ($user->id === 1) {
            return;
        }

        $user->syncRoles([]);
        $user->permissions()->detach();
        $user->delete();
    }

    protected function isDemoOrTestStaffUser(User $user): bool
    {
        if ($user->id === 1 || $user->customer_id !== null) {
            return false;
        }

        $email = strtolower($user->email);

        return str_ends_with($email, '@example.com')
            || (str_ends_with($email, '@janaprints.local') && ! $user->is_active);
    }

    protected function assertCanPurge(Employee $employee): void
    {
        if ($employee->id === 1 || in_array($employee->employee_number, self::PROTECTED_EMPLOYEE_NUMBERS, true)) {
            throw ValidationException::withMessages([
                'employee' => __('The bootstrap Super Admin employee cannot be deleted.'),
            ]);
        }
    }
}
