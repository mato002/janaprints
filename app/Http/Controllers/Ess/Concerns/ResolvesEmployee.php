<?php

namespace App\Http\Controllers\Ess\Concerns;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ResolvesEmployee
{
    protected function essUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->employee_id !== null && ! $user->isClientPortalAccount(), 403);

        abort_unless($user->can('ess.access'), 403);

        return $user;
    }

    protected function essEmployee(): Employee
    {
        $employee = $this->essUser()->employee;

        abort_unless($employee instanceof Employee, 403, __('Your account is not linked to an employee record.'));

        return $employee->loadMissing(['branch', 'department', 'jobTitle', 'user']);
    }

    protected function assertOwnEmployee(Model $model, Employee $employee, string $column = 'employee_id'): void
    {
        abort_unless(
            isset($model->{$column}) && (int) $model->{$column} === (int) $employee->getKey(),
            403,
        );
    }
}
