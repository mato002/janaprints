<?php

namespace Tests\Feature\Ess;

use App\Enums\CommunicationLogChannel;
use App\Enums\CommunicationLogStatus;
use App\Enums\CommunicationLogType;
use App\Enums\EmployeeDocumentCategory;
use App\Enums\EmploymentStatus;
use App\Enums\PayrollRunStatus;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\CommunicationRecipient;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Models\UserSessionRecord;
use App\Support\Hr\EmployeeDocumentService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EssPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
    }

    public function test_employee_sees_own_profile(): void
    {
        [$employee, $user] = $this->essEmployeeAccount('EMP-ESS-01', 'ess.profile@janaprints.test');

        $this->actingAsEss($user)
            ->get(route('ess.dashboard', ['tab' => 'overview']))
            ->assertOk()
            ->assertSee($employee->employee_number)
            ->assertSee($employee->full_name);
    }

    public function test_employee_cannot_access_another_employees_payslip(): void
    {
        [, $user] = $this->essEmployeeAccount('EMP-ESS-02', 'ess.a@janaprints.test');
        $other = $this->essEmployeeAccount('EMP-ESS-03', 'ess.b@janaprints.test')[0];
        $otherPayslip = $this->releasedPayslip($other);

        $this->actingAsEss($user)
            ->get(route('ess.payslips.download', $otherPayslip))
            ->assertForbidden();
    }

    public function test_employee_sees_own_payslips_only(): void
    {
        [$employee, $user] = $this->essEmployeeAccount('EMP-ESS-04', 'ess.payslip@janaprints.test');
        $other = $this->essEmployeeAccount('EMP-ESS-05', 'ess.other@janaprints.test')[0];

        $own = $this->releasedPayslip($employee, 'PS-OWN-001');
        $this->releasedPayslip($other, 'PS-OTHER-001');
        $this->unreleasedPayslip($employee, 'PS-UNRELEASED');

        $response = $this->actingAsEss($user)
            ->get(route('ess.dashboard', ['tab' => 'payslips']))
            ->assertOk();

        $response->assertSee('PS-OWN-001');
        $response->assertDontSee('PS-OTHER-001');
        $response->assertDontSee('PS-UNRELEASED');
    }

    public function test_payslip_download_works(): void
    {
        [$employee, $user] = $this->essEmployeeAccount('EMP-ESS-06', 'ess.download@janaprints.test');
        $payslip = $this->releasedPayslip($employee, 'PS-DL-001');

        $this->actingAsEss($user)
            ->get(route('ess.payslips.download', $payslip))
            ->assertOk();
    }

    public function test_payroll_history_renders(): void
    {
        [$employee, $user] = $this->essEmployeeAccount('EMP-ESS-07', 'ess.history@janaprints.test');
        $this->releasedPayslip($employee, 'PS-HIST-001', gross: 80000, net: 65000, deductions: 15000);

        $this->actingAsEss($user)
            ->get(route('ess.dashboard', ['tab' => 'payroll-history']))
            ->assertOk()
            ->assertSee('80,000.00')
            ->assertSee('65,000.00');
    }

    public function test_documents_restricted_correctly(): void
    {
        [$employee, $user] = $this->essEmployeeAccount('EMP-ESS-08', 'ess.docs@janaprints.test');
        $other = $this->essEmployeeAccount('EMP-ESS-09', 'ess.docs.other@janaprints.test')[0];
        $hr = $this->hrUser();

        $visible = app(EmployeeDocumentService::class)->create(
            $employee->company_id,
            [
                'employee_id' => $employee->id,
                'category' => EmployeeDocumentCategory::Contract->value,
                'title' => 'Employment Contract',
            ],
            UploadedFile::fake()->create('contract.pdf', 50, 'application/pdf'),
            $hr,
        );

        $hidden = app(EmployeeDocumentService::class)->create(
            $employee->company_id,
            [
                'employee_id' => $employee->id,
                'category' => EmployeeDocumentCategory::WarningLetter->value,
                'title' => 'Confidential Warning',
            ],
            UploadedFile::fake()->create('warning.pdf', 50, 'application/pdf'),
            $hr,
        );

        $otherDoc = app(EmployeeDocumentService::class)->create(
            $other->company_id,
            [
                'employee_id' => $other->id,
                'category' => EmployeeDocumentCategory::Contract->value,
                'title' => 'Other Contract',
            ],
            UploadedFile::fake()->create('other.pdf', 50, 'application/pdf'),
            $hr,
        );

        $this->actingAsEss($user)
            ->get(route('ess.dashboard', ['tab' => 'documents']))
            ->assertOk()
            ->assertSee('Employment Contract')
            ->assertDontSee('Confidential Warning');

        $this->actingAsEss($user)
            ->get(route('ess.documents.download', $visible))
            ->assertOk();

        $this->actingAsEss($user)
            ->get(route('ess.documents.download', $hidden))
            ->assertForbidden();

        $this->actingAsEss($user)
            ->get(route('ess.documents.download', $otherDoc))
            ->assertForbidden();
    }

    public function test_password_change_works(): void
    {
        [, $user] = $this->essEmployeeAccount('EMP-ESS-10', 'ess.password@janaprints.test', 'OldPass123!');
        $user->update(['password' => Hash::make('OldPass123!')]);

        $this->actingAsEss($user)
            ->from(route('ess.dashboard', ['tab' => 'security']))
            ->put(route('ess.security.password.update'), [
                'current_password' => 'OldPass123!',
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ])
            ->assertRedirect(route('ess.dashboard', ['tab' => 'security']));

        $user->refresh();
        $this->assertTrue(Hash::check('NewPass123!', $user->password));
    }

    public function test_session_termination_works(): void
    {
        [, $user] = $this->essEmployeeAccount('EMP-ESS-11', 'ess.sessions@janaprints.test');

        UserSessionRecord::query()->create([
            'user_id' => $user->id,
            'session_id' => 'other-session-id',
            'ip_address' => '192.168.1.50',
            'user_agent' => 'Test Browser',
            'last_activity_at' => now()->subHour(),
        ]);

        $this->actingAsEss($user)
            ->from(route('ess.dashboard', ['tab' => 'security']))
            ->post(route('ess.security.sessions.destroy-others'))
            ->assertRedirect(route('ess.dashboard', ['tab' => 'security']))
            ->assertSessionHas('status');
    }

    public function test_communications_visible(): void
    {
        [$employee, $user] = $this->essEmployeeAccount('EMP-ESS-12', 'ess.comms@janaprints.test');

        $log = CommunicationLog::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'reference_number' => 'CL-ESS-001',
            'channel' => CommunicationLogChannel::Email,
            'communication_type' => CommunicationLogType::Transactional,
            'subject' => 'Welcome to Jana Prints',
            'message_body' => 'Your onboarding email.',
            'status' => CommunicationLogStatus::Sent,
            'sent_at' => now(),
        ]);

        CommunicationRecipient::query()->create([
            'communication_log_id' => $log->id,
            'recipient_type' => Employee::class,
            'recipient_id' => $employee->id,
            'email' => $user->email,
            'delivery_status' => CommunicationLogStatus::Sent,
        ]);

        $this->actingAsEss($user)
            ->get(route('ess.dashboard', ['tab' => 'communications']))
            ->assertOk()
            ->assertSee('Welcome to Jana Prints');
    }

    public function test_profile_updates_audited(): void
    {
        [$employee, $user] = $this->essEmployeeAccount('EMP-ESS-13', 'ess.audit@janaprints.test');

        $this->actingAsEss($user)
            ->put(route('ess.profile.update'), [
                'phone' => '0712345678',
                'address' => 'Nairobi, Kenya',
            ])
            ->assertRedirect(route('ess.dashboard', ['tab' => 'profile']));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'ess_profile_updated',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
        ]);

        $this->assertSame('0712345678', $employee->fresh()->phone);
    }

    public function test_mobile_layout_renders(): void
    {
        [, $user] = $this->essEmployeeAccount('EMP-ESS-14', 'ess.mobile@janaprints.test');

        $this->actingAsEss($user)
            ->get(route('ess.dashboard'))
            ->assertOk()
            ->assertSee('ess-mobile-shell', false);
    }

    /**
     * @return array{0: Employee, 1: User}
     */
    protected function essEmployeeAccount(string $employeeNumber, string $email, ?string $password = null): array
    {
        $department = Department::query()->where('company_id', $this->company->id)->firstOrFail();

        $employee = Employee::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'department_id' => $department->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Ess',
            'last_name' => 'User',
            'email' => $email,
            'employment_status' => EmploymentStatus::Active,
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'employee_id' => $employee->id,
            'email' => $email,
            'password' => Hash::make($password ?? 'Password123!'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole(Role::findByName('Staff', 'web'));

        return [$employee, $user];
    }

    protected function actingAsEss(User $user): self
    {
        return $this->actingAs($user)->withSession(['auth_context' => 'ess']);
    }

    protected function releasedPayslip(
        Employee $employee,
        string $reference = 'PS-TEST',
        float $gross = 50000,
        float $net = 42000,
        float $deductions = 8000,
    ): PayrollPayslip {
        $run = PayrollRun::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'payroll_group' => 'main',
            'reference' => 'PR-'.$reference,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'pay_date' => now()->endOfMonth(),
            'status' => PayrollRunStatus::Paid,
            'created_by' => $this->hrUser()->id,
        ]);

        return PayrollPayslip::query()->create([
            'company_id' => $this->company->id,
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'reference' => $reference,
            'basic_salary' => $gross * 0.8,
            'total_allowances' => $gross * 0.2,
            'gross_pay' => $gross,
            'total_deductions' => $deductions,
            'net_pay' => $net,
            'released_at' => now(),
        ]);
    }

    protected function unreleasedPayslip(Employee $employee, string $reference): PayrollPayslip
    {
        $run = PayrollRun::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'payroll_group' => 'main',
            'reference' => 'PR-'.$reference,
            'period_start' => now()->subMonth()->startOfMonth(),
            'period_end' => now()->subMonth()->endOfMonth(),
            'pay_date' => now()->subMonth()->endOfMonth(),
            'status' => PayrollRunStatus::Draft,
            'created_by' => $this->hrUser()->id,
        ]);

        return PayrollPayslip::query()->create([
            'company_id' => $this->company->id,
            'payroll_run_id' => $run->id,
            'employee_id' => $employee->id,
            'reference' => $reference,
            'basic_salary' => 40000,
            'gross_pay' => 50000,
            'total_deductions' => 8000,
            'net_pay' => 42000,
            'released_at' => null,
        ]);
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }
}
