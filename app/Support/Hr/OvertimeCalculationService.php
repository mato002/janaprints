<?php

namespace App\Support\Hr;

use App\Enums\AttendanceStatus;
use App\Models\Hr\Shift;
use Illuminate\Support\Carbon;

class OvertimeCalculationService
{
    public function scheduledHours(?Shift $shift): float
    {
        if ($shift === null) {
            return 8.0;
        }

        $start = $this->parseTimeOnDate($shift->start_time, Carbon::today());
        $end = $this->parseTimeOnDate($shift->end_time, Carbon::today());

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $minutes = $start->diffInMinutes($end) - (int) $shift->break_minutes;

        return round(max(0, $minutes) / 60, 2);
    }

    public function actualHours(?Carbon $clockIn, ?Carbon $clockOut, int $breakMinutes = 0): ?float
    {
        if ($clockIn === null || $clockOut === null || $clockOut->lessThanOrEqualTo($clockIn)) {
            return null;
        }

        $minutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;

        return round(max(0, $minutes) / 60, 2);
    }

    public function lateMinutes(?Carbon $clockIn, ?Shift $shift, Carbon $attendanceDate): int
    {
        if ($clockIn === null || $shift === null) {
            return 0;
        }

        $scheduledStart = $this->parseTimeOnDate($shift->start_time, $attendanceDate)
            ->addMinutes((int) $shift->grace_minutes);

        if ($clockIn->greaterThan($scheduledStart)) {
            return (int) $scheduledStart->diffInMinutes($clockIn);
        }

        return 0;
    }

    public function overtimeHours(float $scheduledHours, ?float $actualHours): float
    {
        if ($actualHours === null) {
            return 0.0;
        }

        return round(max(0, $actualHours - $scheduledHours), 2);
    }

    public function resolveStatus(
        ?Carbon $clockIn,
        ?Carbon $clockOut,
        int $lateMinutes,
        ?AttendanceStatus $override = null,
    ): AttendanceStatus {
        if ($override !== null) {
            return $override;
        }

        if ($clockIn === null) {
            return AttendanceStatus::Absent;
        }

        if ($clockOut === null) {
            return $lateMinutes > 0 ? AttendanceStatus::Late : AttendanceStatus::Present;
        }

        $actual = $this->actualHours($clockIn, $clockOut);

        if ($actual !== null && $actual < 4) {
            return AttendanceStatus::HalfDay;
        }

        return $lateMinutes > 0 ? AttendanceStatus::Late : AttendanceStatus::Present;
    }

    protected function parseTimeOnDate(mixed $time, Carbon $date): Carbon
    {
        $timeString = $time instanceof Carbon
            ? $time->format('H:i:s')
            : (string) $time;

        return Carbon::parse($date->toDateString().' '.$timeString);
    }
}
