<?php

namespace Tests\Feature\Admin;

use App\Enums\LeaveAccrualFrequency;
use App\Enums\LeaveUnit;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Hr\LeaveAccrualRule;
use App\Models\Hr\LeaveCarryForwardRule;
use App\Models\Hr\LeavePolicy;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PublicHoliday;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_configuration_center_renders(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.leave.config'))
            ->assertOk()
            ->assertSee(__('Leave Configuration'))
            ->assertSee(__('Leave Types'))
            ->assertSee(__('Public Holidays'));
    }

    public function test_leave_type_crud_via_service(): void
    {
        $hr = $this->hrUser();
        $companyId = $hr->company_id;

        $this->actingAs($hr)
            ->post(route('admin.hr.leave.config.types.store'), [
                'code' => 'CUSTOM',
                'name' => 'Custom Leave',
                'unit' => LeaveUnit::Days->value,
                'default_days_per_year' => 5,
                'is_paid' => 1,
                'requires_supervisor_approval' => 1,
                'requires_hr_approval' => 1,
            ])
            ->assertRedirect();

        $type = LeaveType::query()->where('code', 'CUSTOM')->first();
        $this->assertNotNull($type);
        $this->assertSame('Custom Leave', $type->name);
        $this->assertSame(LeaveUnit::Days->value, $type->unit instanceof LeaveUnit ? $type->unit->value : $type->unit);

        $this->actingAs($hr)
            ->put(route('admin.hr.leave.config.types.update', $type), [
                'code' => 'CUSTOM',
                'name' => 'Custom Leave Updated',
                'unit' => LeaveUnit::Hours->value,
                'is_paid' => 0,
                'requires_supervisor_approval' => 0,
                'requires_hr_approval' => 1,
            ])
            ->assertRedirect();

        $type->refresh();
        $this->assertSame('Custom Leave Updated', $type->name);
        $this->assertSame(LeaveUnit::Hours->value, $type->unit instanceof LeaveUnit ? $type->unit->value : $type->unit);
        $this->assertFalse($type->is_paid);
    }

    public function test_holiday_crud(): void
    {
        $hr = $this->hrUser();
        $branch = Branch::query()->where('company_id', $hr->company_id)->first();

        $this->actingAs($hr)
            ->post(route('admin.hr.leave.config.holidays.store'), [
                'name' => 'Jamhuri Day',
                'holiday_date' => '2026-12-12',
                'region' => 'National',
                'branch_id' => $branch->id,
                'is_recurring' => 1,
            ])
            ->assertRedirect();

        $holiday = PublicHoliday::query()->where('name', 'Jamhuri Day')->first();
        $this->assertNotNull($holiday);
        $this->assertTrue($holiday->is_recurring);
        $this->assertSame('National', $holiday->region);

        $this->actingAs($hr)
            ->put(route('admin.hr.leave.config.holidays.update', $holiday), [
                'name' => 'Jamhuri Day (Updated)',
                'holiday_date' => '2026-12-12',
                'region' => 'Kenya',
                'is_recurring' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $holiday->refresh();
        $this->assertSame('Jamhuri Day (Updated)', $holiday->name);
    }

    public function test_accrual_rule_create(): void
    {
        $hr = $this->hrUser();
        $leaveType = LeaveType::query()->where('company_id', $hr->company_id)->where('code', 'ANNUAL')->first();

        $this->actingAs($hr)
            ->post(route('admin.hr.leave.config.accrual.store'), [
                'leave_type_id' => $leaveType->id,
                'frequency' => LeaveAccrualFrequency::Monthly->value,
                'rate_per_period' => 1.75,
                'effective_from' => now()->startOfYear()->toDateString(),
            ])
            ->assertRedirect();

        $rule = LeaveAccrualRule::query()->where('leave_type_id', $leaveType->id)->first();
        $this->assertNotNull($rule);
        $this->assertSame(LeaveAccrualFrequency::Monthly, $rule->frequency);
        $this->assertSame('1.75', $rule->rate_per_period);
    }

    public function test_carry_forward_rule_create(): void
    {
        $hr = $this->hrUser();
        $leaveType = LeaveType::query()->where('company_id', $hr->company_id)->where('code', 'ANNUAL')->first();

        $this->actingAs($hr)
            ->post(route('admin.hr.leave.config.carry.store'), [
                'leave_type_id' => $leaveType->id,
                'max_carry_days' => 7,
                'expiry_month' => 3,
                'expiry_day' => 31,
                'policy_notes' => 'Use by end of Q1',
            ])
            ->assertRedirect();

        $rule = LeaveCarryForwardRule::query()->where('leave_type_id', $leaveType->id)->first();
        $this->assertNotNull($rule);
        $this->assertSame('7.0', $rule->max_carry_days);
        $this->assertSame(3, $rule->expiry_month);
    }

    public function test_leave_policy_create(): void
    {
        $hr = $this->hrUser();
        $leaveType = LeaveType::query()->where('company_id', $hr->company_id)->first();

        $this->actingAs($hr)
            ->post(route('admin.hr.leave.config.policies.store'), [
                'leave_type_id' => $leaveType->id,
                'code' => 'ANNUAL_STD',
                'name' => 'Standard Annual Policy',
                'min_notice_days' => 7,
                'max_consecutive_days' => 14,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leave_policies', [
            'company_id' => $hr->company_id,
            'code' => 'ANNUAL_STD',
            'leave_type_id' => $leaveType->id,
        ]);
    }

    public function test_viewer_without_permission_is_forbidden(): void
    {
        $company = Company::query()->first();
        $role = Role::findOrCreate('No Leave Config', 'web');
        $role->syncPermissions([]);

        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.leave.config'))
            ->assertForbidden();
    }

    protected function hrUser(): User
    {
        $user = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'HR'))->first()
            ?? User::factory()->create(['company_id' => Company::query()->value('id')]);

        if (! $user->hasRole('HR')) {
            $user->assignRole(Role::findByName('HR', 'web'));
        }

        return $user;
    }
}
