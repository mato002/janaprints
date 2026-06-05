<?php

namespace App\Support\Hr;

use App\Enums\LeaveRequestStatus;
use App\Models\Employee;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\PublicHoliday;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeaveConflictService
{
    /**
     * @return list<array{type: string, message: string}>
     */
    public function check(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeRequestId = null,
    ): array {
        $warnings = [];

        $warnings = array_merge($warnings, $this->checkExistingLeave($employee, $startDate, $endDate, $excludeRequestId));
        $warnings = array_merge($warnings, $this->checkPublicHolidays($employee, $startDate, $endDate));
        $warnings = array_merge($warnings, $this->checkDepartmentStaffing($employee, $startDate, $endDate, $excludeRequestId));

        return $warnings;
    }

    /**
     * @return list<array{type: string, message: string}>
     */
    protected function checkExistingLeave(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeRequestId,
    ): array {
        $overlap = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', [
                LeaveRequestStatus::Submitted->value,
                LeaveRequestStatus::SupervisorApproved->value,
                LeaveRequestStatus::Approved->value,
            ])
            ->when($excludeRequestId, fn ($q) => $q->where('id', '!=', $excludeRequestId))
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($inner) use ($startDate, $endDate) {
                        $inner->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        if ($overlap) {
            return [[
                'type' => 'existing_leave',
                'message' => __('Employee already has overlapping leave during this period.'),
            ]];
        }

        return [];
    }

    /**
     * @return list<array{type: string, message: string}>
     */
    protected function checkPublicHolidays(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $holidays = PublicHoliday::query()
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->whereBetween('holiday_date', [$startDate, $endDate])
            ->where(function ($query) use ($employee) {
                $query->whereNull('branch_id')
                    ->orWhere('branch_id', $employee->branch_id);
            })
            ->get();

        return $holidays->map(fn (PublicHoliday $holiday) => [
            'type' => 'public_holiday',
            'message' => __('Public holiday on :date: :name', [
                'date' => $holiday->holiday_date->format('Y-m-d'),
                'name' => $holiday->name,
            ]),
        ])->all();
    }

    /**
     * @return list<array{type: string, message: string}>
     */
    protected function checkDepartmentStaffing(
        Employee $employee,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeRequestId,
    ): array {
        if ($employee->department_id === null) {
            return [];
        }

        $totalStaff = Employee::query()
            ->where('department_id', $employee->department_id)
            ->where('is_active', true)
            ->count();

        if ($totalStaff < 2) {
            return [];
        }

        $onLeave = LeaveRequest::query()
            ->where('department_id', $employee->department_id)
            ->where('employee_id', '!=', $employee->id)
            ->whereIn('status', [
                LeaveRequestStatus::Submitted->value,
                LeaveRequestStatus::SupervisorApproved->value,
                LeaveRequestStatus::Approved->value,
            ])
            ->when($excludeRequestId, fn ($q) => $q->where('id', '!=', $excludeRequestId))
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($inner) use ($startDate, $endDate) {
                        $inner->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->distinct('employee_id')
            ->count('employee_id');

        $threshold = max(1, (int) floor($totalStaff * 0.5));

        if ($onLeave >= $threshold) {
            return [[
                'type' => 'department_staffing',
                'message' => __('Department staffing risk: :count of :total staff already on leave.', [
                    'count' => $onLeave,
                    'total' => $totalStaff,
                ]),
            ]];
        }

        return [];
    }
}
