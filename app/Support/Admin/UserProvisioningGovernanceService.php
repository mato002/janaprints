<?php

namespace App\Support\Admin;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserProvisioningGovernanceService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function validateCreate(array $data, int $companyId): void
    {
        $employeeId = filled($data['employee_id'] ?? null) ? (int) $data['employee_id'] : null;
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $isSystemAccount = (bool) ($data['system_account'] ?? false);

        if ($employeeId && $isSystemAccount) {
            throw ValidationException::withMessages([
                'system_account' => __('A system account cannot be linked to an employee record.'),
            ]);
        }

        if (! $employeeId && ! $isSystemAccount) {
            throw ValidationException::withMessages([
                'system_account' => __('Staff must be onboarded via HR → People → Create employee. Check “System account” only for logins that will never have an employee record.'),
            ]);
        }

        $employeeByEmail = $this->findEmployeeByEmail($companyId, $email);

        if ($employeeByEmail !== null) {
            if ($employeeId === null || $employeeId !== $employeeByEmail->id) {
                throw ValidationException::withMessages([
                    'email' => __('This email belongs to employee :name (:number). Onboard them via HR → Create employee, or link that employee when creating this user.', [
                        'name' => $employeeByEmail->full_name,
                        'number' => $employeeByEmail->employee_number,
                    ]),
                ]);
            }
        }

        if ($employeeId !== null) {
            $this->assertLinkableEmployee($employeeId, $companyId, $email);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function validateUpdate(User $user, array $data, int $companyId): void
    {
        $employeeId = array_key_exists('employee_id', $data)
            ? (filled($data['employee_id']) ? (int) $data['employee_id'] : null)
            : $user->employee_id;

        $email = strtolower(trim((string) ($data['email'] ?? $user->email)));

        if ($user->employee_id !== null && $employeeId === null) {
            throw ValidationException::withMessages([
                'employee_id' => __('Staff login accounts cannot be unlinked from their employee record. Manage the person under HR → Employees.'),
            ]);
        }

        $employeeByEmail = $this->findEmployeeByEmail($companyId, $email);

        if ($employeeByEmail !== null) {
            if ($employeeId === null || $employeeId !== $employeeByEmail->id) {
                throw ValidationException::withMessages([
                    'email' => __('This email belongs to employee :name (:number). Link that employee or use HR onboarding instead.', [
                        'name' => $employeeByEmail->full_name,
                        'number' => $employeeByEmail->employee_number,
                    ]),
                ]);
            }
        }

        if ($employeeId !== null && (int) $employeeId !== (int) $user->employee_id) {
            $this->assertLinkableEmployee($employeeId, $companyId, $email, $user->id);
        } elseif ($employeeId !== null) {
            $this->assertEmailMatchesEmployee($employeeId, $companyId, $email);
            $this->assertEmployeeNotLinkedElsewhere($employeeId, $companyId, $user->id);
        }
    }

    protected function findEmployeeByEmail(int $companyId, string $email): ?Employee
    {
        if ($email === '') {
            return null;
        }

        return Employee::query()
            ->where('company_id', $companyId)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    protected function assertLinkableEmployee(
        int $employeeId,
        int $companyId,
        string $email,
        ?int $ignoreUserId = null,
    ): void {
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->find($employeeId);

        if ($employee === null) {
            throw ValidationException::withMessages([
                'employee_id' => __('The selected employee is not valid for this company.'),
            ]);
        }

        $this->assertEmailMatchesEmployee($employeeId, $companyId, $email);
        $this->assertEmployeeNotLinkedElsewhere($employeeId, $companyId, $ignoreUserId);
    }

    protected function assertEmailMatchesEmployee(int $employeeId, int $companyId, string $email): void
    {
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->find($employeeId);

        if ($employee === null || ! filled($employee->email)) {
            return;
        }

        if (strtolower(trim($employee->email)) !== $email) {
            throw ValidationException::withMessages([
                'email' => __('The login email must match the employee personal email (:email).', [
                    'email' => $employee->email,
                ]),
            ]);
        }
    }

    protected function assertEmployeeNotLinkedElsewhere(
        int $employeeId,
        int $companyId,
        ?int $ignoreUserId = null,
    ): void {
        $existingUser = User::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->when($ignoreUserId, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->first();

        if ($existingUser !== null) {
            throw ValidationException::withMessages([
                'employee_id' => __('This employee already has a login account. Use HR → Employees to resend activation or manage access.'),
            ]);
        }
    }
}
