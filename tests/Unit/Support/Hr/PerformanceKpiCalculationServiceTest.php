<?php

namespace Tests\Unit\Support\Hr;

use App\Enums\AttendanceStatus;
use App\Enums\PerformanceRating;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\EmployeeSalesTarget;
use App\Support\Hr\PerformanceKpiCalculationService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PerformanceKpiCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PerformanceKpiCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->service = app(PerformanceKpiCalculationService::class);
    }

    public function test_calculates_attendance_percent_from_records(): void
    {
        $employee = $this->testEmployee();
        $start = Carbon::parse('2026-01-01');
        $end = Carbon::parse('2026-01-09');

        foreach (['2026-01-02', '2026-01-03', '2026-01-06', '2026-01-07', '2026-01-08'] as $date) {
            AttendanceRecord::query()->create([
                'company_id' => $employee->company_id,
                'branch_id' => $employee->branch_id,
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'status' => AttendanceStatus::Present,
            ]);
        }

        $percent = $this->service->attendancePercent($employee, $start, $end);

        $this->assertGreaterThan(0, $percent);
        $this->assertLessThanOrEqual(100, $percent);
    }

    public function test_sales_target_kpi_uses_employee_targets(): void
    {
        $employee = $this->testEmployee();
        $start = Carbon::parse('2026-01-01');
        $end = Carbon::parse('2026-03-31');

        EmployeeSalesTarget::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'period_start' => '2026-01-01',
            'period_end' => '2026-03-31',
            'target_amount' => 500000,
        ]);

        $this->assertSame(500000.0, $this->service->salesTarget($employee, $start, $end));
    }

    public function test_suggest_rating_maps_composite_score(): void
    {
        $this->assertSame(PerformanceRating::Excellent, $this->service->suggestRating(92));
        $this->assertSame(PerformanceRating::Good, $this->service->suggestRating(80));
        $this->assertSame(PerformanceRating::Average, $this->service->suggestRating(65));
        $this->assertSame(PerformanceRating::Poor, $this->service->suggestRating(45));
        $this->assertSame(PerformanceRating::Critical, $this->service->suggestRating(20));
    }

    protected function testEmployee(): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-PERF-001',
            'first_name' => 'Performance',
            'last_name' => 'Staff',
            'email' => 'performance.staff@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }
}
