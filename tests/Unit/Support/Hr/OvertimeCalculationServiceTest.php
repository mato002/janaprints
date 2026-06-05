<?php

namespace Tests\Unit\Support\Hr;

use App\Enums\AttendanceStatus;
use App\Models\Hr\Shift;
use App\Support\Hr\OvertimeCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OvertimeCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private OvertimeCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OvertimeCalculationService::class);
    }

    public function test_scheduled_hours_for_day_shift(): void
    {
        $shift = new Shift([
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'break_minutes' => 60,
        ]);

        $this->assertSame(8.0, $this->service->scheduledHours($shift));
    }

    public function test_actual_hours_subtracts_break_minutes(): void
    {
        $clockIn = Carbon::parse('2026-06-05 08:00:00');
        $clockOut = Carbon::parse('2026-06-05 17:00:00');

        $this->assertSame(8.0, $this->service->actualHours($clockIn, $clockOut, 60));
    }

    public function test_overtime_hours_when_actual_exceeds_scheduled(): void
    {
        $this->assertSame(2.0, $this->service->overtimeHours(8.0, 10.0));
        $this->assertSame(0.0, $this->service->overtimeHours(8.0, 7.5));
    }

    public function test_late_minutes_after_grace_period(): void
    {
        $shift = new Shift([
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'grace_minutes' => 15,
            'break_minutes' => 60,
        ]);

        $clockIn = Carbon::parse('2026-06-05 08:30:00');

        $this->assertSame(15, $this->service->lateMinutes($clockIn, $shift, Carbon::parse('2026-06-05')));
    }

    public function test_resolve_status_marks_half_day_for_short_hours(): void
    {
        $clockIn = Carbon::parse('2026-06-05 08:00:00');
        $clockOut = Carbon::parse('2026-06-05 11:00:00');

        $status = $this->service->resolveStatus($clockIn, $clockOut, 0);

        $this->assertSame(AttendanceStatus::HalfDay, $status);
    }
}
