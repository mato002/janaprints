<?php

namespace App\Support\Hr;

use App\Enums\AttendanceCorrectionType;
use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\EmploymentStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceCorrection;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\Shift;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(
        protected OvertimeCalculationService $overtime,
        protected ShiftService $shifts,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, int|float>
     */
    public function dashboardMetrics(int $companyId, ?Carbon $date = null, array $filters = []): array
    {
        $date ??= Carbon::today();
        $employeeQuery = $this->employeeScope($companyId, $filters);

        $totalEmployees = (clone $employeeQuery)->count();

        $recordsQuery = AttendanceRecord::query()
            ->where('company_id', $companyId)
            ->whereDate('attendance_date', $date);

        $this->applyRecordFilters($recordsQuery, $filters);

        $present = (clone $recordsQuery)->whereIn('status', [
            AttendanceStatus::Present->value,
            AttendanceStatus::Late->value,
        ])->count();

        $absent = (clone $recordsQuery)->where('status', AttendanceStatus::Absent->value)->count();

        $late = (clone $recordsQuery)->where(function (Builder $query) {
            $query->where('status', AttendanceStatus::Late->value)
                ->orWhere('late_minutes', '>', 0);
        })->count();

        $onLeave = (clone $recordsQuery)->where('status', AttendanceStatus::Leave->value)->count()
            + (clone $employeeQuery)->where('employment_status', EmploymentStatus::OnLeave->value)
                ->whereDoesntHave('attendanceRecords', fn (Builder $q) => $q->whereDate('attendance_date', $date))
                ->count();

        $attendancePercent = $totalEmployees > 0
            ? round(($present / $totalEmployees) * 100, 1)
            : 0.0;

        return [
            'present_today' => $present,
            'absent_today' => $absent,
            'late_today' => $late,
            'on_leave' => $onLeave,
            'total_employees' => $totalEmployees,
            'attendance_percent' => $attendancePercent,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateRegister(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AttendanceRecord::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['employee', 'branch', 'department', 'shift']);

        $this->applyRecordFilters($query, $filters);

        if (! empty($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        } elseif (! empty($filters['date_from'])) {
            $query->whereDate('attendance_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('attendance_date', '<=', $filters['date_to']);
        }

        return $query
            ->orderByDesc('attendance_date')
            ->orderBy('employee_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Collection<int, AttendanceRecord>
     */
    public function exportRows(int $companyId, array $filters = [])
    {
        $query = AttendanceRecord::query()
            ->where('company_id', $companyId)
            ->with(['employee', 'branch', 'department', 'shift']);

        $this->applyRecordFilters($query, $filters);

        if (! empty($filters['date'])) {
            $query->whereDate('attendance_date', $filters['date']);
        }

        return $query->orderByDesc('attendance_date')->orderBy('employee_id')->get();
    }

    public function clockIn(Employee $employee, User $user, Request $request): AttendanceRecord
    {
        $now = now();
        $date = $now->toDateString();
        $shift = $this->resolveShift($employee, $request->input('shift_id'));

        $existing = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $date)
            ->first();

        if ($existing?->clock_in_at !== null && $existing->clock_out_at === null) {
            throw ValidationException::withMessages([
                'clock_in' => __('Employee is already clocked in.'),
            ]);
        }

        $lateMinutes = $this->overtime->lateMinutes($now, $shift, Carbon::parse($date));
        $scheduledHours = $this->overtime->scheduledHours($shift);

        $record = $existing ?? new AttendanceRecord([
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'employee_id' => $employee->id,
            'attendance_date' => $date,
        ]);

        $record->fill([
            'shift_id' => $shift?->id,
            'clock_in_at' => $now,
            'clock_in_device' => $request->userAgent(),
            'clock_in_ip' => $request->ip(),
            'clock_in_location' => $request->input('location'),
            'scheduled_hours' => $scheduledHours,
            'late_minutes' => $lateMinutes,
            'status' => $this->overtime->resolveStatus($now, $record->clock_out_at, $lateMinutes),
            'method' => AttendanceMethod::Clock,
            'is_manual' => false,
        ]);

        $record->save();

        return $record->fresh(['employee', 'shift', 'branch', 'department']);
    }

    public function clockOut(Employee $employee, User $user, Request $request): AttendanceRecord
    {
        $now = now();
        $date = $now->toDateString();

        $record = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $date)
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->first();

        if ($record === null) {
            throw ValidationException::withMessages([
                'clock_out' => __('No open clock-in found for today.'),
            ]);
        }

        $shift = $record->shift;
        $breakMinutes = (int) ($shift?->break_minutes ?? 0);
        $actualHours = $this->overtime->actualHours($record->clock_in_at, $now, $breakMinutes);
        $overtimeHours = $this->overtime->overtimeHours((float) $record->scheduled_hours, $actualHours);

        $record->fill([
            'clock_out_at' => $now,
            'clock_out_device' => $request->userAgent(),
            'clock_out_ip' => $request->ip(),
            'clock_out_location' => $request->input('location'),
            'actual_hours' => $actualHours,
            'overtime_hours' => $overtimeHours,
            'status' => $this->overtime->resolveStatus(
                $record->clock_in_at,
                $now,
                (int) $record->late_minutes,
            ),
        ]);

        $record->save();

        return $record->fresh(['employee', 'shift', 'branch', 'department']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createManual(array $data, User $user): AttendanceRecord
    {
        $employee = Employee::query()->findOrFail($data['employee_id']);
        $shift = $this->resolveShift($employee, $data['shift_id'] ?? null);
        $date = Carbon::parse($data['attendance_date']);
        $status = AttendanceStatus::from($data['status']);
        $clockIn = ! empty($data['clock_in_at']) ? Carbon::parse($data['clock_in_at']) : null;
        $clockOut = ! empty($data['clock_out_at']) ? Carbon::parse($data['clock_out_at']) : null;

        $scheduledHours = $this->overtime->scheduledHours($shift);
        $breakMinutes = (int) ($shift?->break_minutes ?? 0);
        $actualHours = $this->overtime->actualHours($clockIn, $clockOut, $breakMinutes);
        $lateMinutes = $this->overtime->lateMinutes($clockIn, $shift, $date);
        $overtimeHours = $this->overtime->overtimeHours($scheduledHours, $actualHours);

        $record = AttendanceRecord::query()->updateOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => $date->toDateString(),
            ],
            [
                'company_id' => $employee->company_id,
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
                'shift_id' => $shift?->id,
                'clock_in_at' => $clockIn,
                'clock_out_at' => $clockOut,
                'scheduled_hours' => $scheduledHours,
                'actual_hours' => $actualHours,
                'late_minutes' => $lateMinutes,
                'overtime_hours' => $overtimeHours,
                'status' => $this->overtime->resolveStatus($clockIn, $clockOut, $lateMinutes, $status),
                'method' => AttendanceMethod::Manual,
                'notes' => $data['notes'] ?? null,
                'is_manual' => true,
                'adjusted_by_user_id' => $user->id,
            ],
        );

        return $record->fresh(['employee', 'shift', 'branch', 'department']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function adjust(AttendanceRecord $record, array $data, User $user, bool $requiresApproval = false): AttendanceCorrection
    {
        $correctionType = AttendanceCorrectionType::from($data['correction_type']);
        $clockIn = array_key_exists('clock_in_at', $data) && $data['clock_in_at'] !== null
            ? Carbon::parse($data['clock_in_at'])
            : $record->clock_in_at;
        $clockOut = array_key_exists('clock_out_at', $data) && $data['clock_out_at'] !== null
            ? Carbon::parse($data['clock_out_at'])
            : $record->clock_out_at;
        $newStatus = isset($data['status'])
            ? AttendanceStatus::from($data['status'])
            : null;

        $correction = AttendanceCorrection::query()->create([
            'company_id' => $record->company_id,
            'attendance_record_id' => $record->id,
            'corrected_by_user_id' => $user->id,
            'correction_type' => $correctionType,
            'reason' => $data['reason'],
            'previous_clock_in_at' => $record->clock_in_at,
            'previous_clock_out_at' => $record->clock_out_at,
            'previous_status' => $record->status,
            'new_clock_in_at' => $clockIn,
            'new_clock_out_at' => $clockOut,
            'new_status' => $newStatus,
        ]);

        if (! $requiresApproval || $user->can('hr.attendance.approve')) {
            $this->applyCorrection($record, $correction, $user);
        }

        return $correction->fresh();
    }

    public function applyCorrection(AttendanceRecord $record, AttendanceCorrection $correction, User $user): AttendanceRecord
    {
        $shift = $record->shift;
        $breakMinutes = (int) ($shift?->break_minutes ?? 0);
        $scheduledHours = $this->overtime->scheduledHours($shift);
        $actualHours = $this->overtime->actualHours(
            $correction->new_clock_in_at,
            $correction->new_clock_out_at,
            $breakMinutes,
        );
        $lateMinutes = $this->overtime->lateMinutes(
            $correction->new_clock_in_at,
            $shift,
            $record->attendance_date,
        );

        $record->fill([
            'clock_in_at' => $correction->new_clock_in_at,
            'clock_out_at' => $correction->new_clock_out_at,
            'scheduled_hours' => $scheduledHours,
            'actual_hours' => $actualHours,
            'late_minutes' => $lateMinutes,
            'overtime_hours' => $this->overtime->overtimeHours($scheduledHours, $actualHours),
            'status' => $correction->new_status
                ?? $this->overtime->resolveStatus(
                    $correction->new_clock_in_at,
                    $correction->new_clock_out_at,
                    $lateMinutes,
                ),
            'is_manual' => true,
            'adjusted_by_user_id' => $correction->corrected_by_user_id,
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        $record->save();

        $correction->update([
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        return $record->fresh(['employee', 'shift', 'branch', 'department']);
    }

    public function approveCorrection(AttendanceCorrection $correction, User $user): AttendanceRecord
    {
        return $this->applyCorrection($correction->attendanceRecord, $correction, $user);
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(int $companyId): array
    {
        return [
            'employees' => Employee::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get(),
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'departments' => Department::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'shifts' => Shift::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    protected function resolveShift(Employee $employee, mixed $shiftId): ?Shift
    {
        if ($shiftId) {
            return Shift::query()
                ->where('company_id', $employee->company_id)
                ->where('id', $shiftId)
                ->first();
        }

        return $employee->shift;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function employeeScope(int $companyId, array $filters = []): Builder
    {
        $query = Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('id', $filters['employee_id']);
        }

        return $query;
    }

    /**
     * @param  Builder<AttendanceRecord>  $query
     * @param  array<string, mixed>  $filters
     */
    protected function applyRecordFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['shift_id'])) {
            $query->where('shift_id', $filters['shift_id']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
