<?php

namespace Tests\Unit\Support\Hr;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Hr\LeaveType;
use App\Support\Hr\LeaveBalanceService;
use Database\Seeders\DefaultLeaveTypesSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_available_balance_formula(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $company->branches()->first()->id,
            'employee_number' => 'EMP-BAL-1',
            'first_name' => 'Balance',
            'last_name' => 'Test',
            'employment_status' => 'active',
            'is_active' => true,
        ]);

        $leaveType = LeaveType::query()->where('code', 'ANNUAL')->firstOrFail();
        $service = app(LeaveBalanceService::class);
        $balance = $service->balanceFor($employee, $leaveType);

        $balance->update([
            'opening_balance' => 10,
            'earned' => 5,
            'taken' => 3,
            'pending' => 2,
        ]);

        $this->assertSame(10.0, $balance->available());
    }
}
