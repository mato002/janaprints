<?php

namespace App\Policies;

use App\Models\Hr\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.attendance.view');
    }

    public function view(User $user, AttendanceRecord $record): bool
    {
        return $user->can('hr.attendance.view') && $this->sameCompany($user, $record->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.attendance.create');
    }

    public function update(User $user, AttendanceRecord $record): bool
    {
        return $user->can('hr.attendance.edit') && $this->sameCompany($user, $record->company_id);
    }

    public function adjust(User $user, AttendanceRecord $record): bool
    {
        return $user->can('hr.attendance.edit') && $this->sameCompany($user, $record->company_id);
    }

    public function approve(User $user, AttendanceRecord $record): bool
    {
        return $user->can('hr.attendance.approve') && $this->sameCompany($user, $record->company_id);
    }

    public function export(User $user): bool
    {
        return $user->can('hr.attendance.export');
    }

    public function clock(User $user): bool
    {
        return $user->can('hr.attendance.create') && $user->employee_id !== null;
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}
