<?php

namespace Tests\Feature\Admin;

use App\Enums\BackgroundJobStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Operations\BackgroundJobCancellation;
use App\Models\User;
use App\Services\Operations\BackgroundJobMonitorService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackgroundJobsCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_job_monitoring_dashboard_renders(): void
    {
        $admin = $this->companyAdmin();
        $uuid = (string) Str::uuid();

        DB::table('jobs')->insert([
            'queue' => 'sms',
            'payload' => $this->jobPayload($uuid, 'SendSmsMessageJob'),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.operations.jobs.index'))
            ->assertOk()
            ->assertSee(__('Background Jobs'))
            ->assertSee('sms')
            ->assertSee(__('Pending'));
    }

    public function test_job_monitoring_service_collects_queue_and_failed_jobs(): void
    {
        $uuid = (string) Str::uuid();

        DB::table('jobs')->insert([
            'queue' => 'exports',
            'payload' => $this->jobPayload($uuid, 'ProcessCommercialReportExportJob'),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'emails',
            'payload' => $this->jobPayload((string) Str::uuid(), 'SendEmailJob'),
            'exception' => "RuntimeException: SMTP connection failed\n#0 /app/Jobs/SendEmailJob.php(20)",
            'failed_at' => now(),
        ]);

        $rows = app(BackgroundJobMonitorService::class)->paginate([])->items();

        $this->assertNotEmpty($rows);
        $this->assertTrue(collect($rows)->contains(fn (array $row) => $row['status'] === BackgroundJobStatus::Pending));
        $this->assertTrue(collect($rows)->contains(fn (array $row) => $row['status'] === BackgroundJobStatus::Failed));
    }

    public function test_retry_failed_job(): void
    {
        $admin = $this->companyAdmin();
        $uuid = (string) Str::uuid();

        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'exports',
            'payload' => $this->jobPayload($uuid, 'ProcessCommercialReportExportJob'),
            'exception' => 'RuntimeException: Export failed',
            'failed_at' => now(),
        ]);

        Artisan::shouldReceive('call')
            ->once()
            ->with('queue:retry', ['id' => $uuid])
            ->andReturn(0);

        $this->actingAs($admin)
            ->post(route('admin.operations.jobs.retry', ['reference' => 'failed:'.$uuid]))
            ->assertRedirect(route('admin.operations.jobs.index'))
            ->assertSessionHas('success');
    }

    public function test_cancel_job(): void
    {
        $admin = $this->companyAdmin();
        $uuid = (string) Str::uuid();

        $jobId = DB::table('jobs')->insertGetId([
            'queue' => 'sms',
            'payload' => $this->jobPayload($uuid, 'SendSmsMessageJob'),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.operations.jobs.cancel', ['reference' => 'pending:'.$jobId]))
            ->assertRedirect(route('admin.operations.jobs.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('jobs', ['id' => $jobId]);
        $this->assertDatabaseHas('background_job_cancellations', [
            'queue' => 'sms',
            'job_class' => 'SendSmsMessageJob',
            'cancelled_by' => $admin->id,
        ]);

        $this->assertSame(1, BackgroundJobCancellation::query()->count());
    }

    public function test_permission_enforcement_blocks_unauthorized_access(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Designer');

        $this->actingAs($user)
            ->get(route('admin.operations.jobs.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.operations.jobs.retry', ['reference' => 'failed:abc']))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.operations.jobs.cancel', ['reference' => 'pending:1']))
            ->assertForbidden();
    }

    public function test_viewer_without_retry_permission_cannot_retry_failed_queue(): void
    {
        $viewer = $this->jobsViewer();

        $this->actingAs($viewer)
            ->post(route('admin.operations.jobs.retry-failed'))
            ->assertForbidden();
    }

    public function test_system_operations_section_links_to_background_jobs(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.catalog', ['section' => 'operations']))
            ->assertOk()
            ->assertSee(__('Background Jobs'))
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'operations',
                'tab' => 'background-jobs',
            ]), false);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function jobsViewer(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Viewer');
        $user->givePermissionTo('operations.jobs.view');

        return $user;
    }

    protected function jobPayload(string $uuid, string $displayName): string
    {
        return json_encode([
            'uuid' => $uuid,
            'displayName' => $displayName,
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'attempts' => 0,
        ], JSON_THROW_ON_ERROR);
    }
}
