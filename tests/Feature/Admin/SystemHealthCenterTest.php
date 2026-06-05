<?php

namespace Tests\Feature\Admin;

use App\Enums\SystemHealthStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Operations\SystemHealthService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SystemHealthCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_health_dashboard_renders_for_authorized_admin(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.operations.health.index'))
            ->assertOk()
            ->assertSee(__('System Health'))
            ->assertSee(__('System Status'))
            ->assertSee(__('Database Monitoring'))
            ->assertSee(__('Queue Monitoring'))
            ->assertSee(__('Storage Monitoring'))
            ->assertSee(__('Alert Center'));
    }

    public function test_database_check_reports_connected_metrics(): void
    {
        $metrics = app(SystemHealthService::class)->databaseMetrics();

        $this->assertTrue($metrics['connected']);
        $this->assertSame(__('Connected'), $metrics['connection_status']);
        $this->assertGreaterThan(0, $metrics['table_count']);
        $this->assertNotNull($metrics['response_time_ms']);
        $this->assertSame(SystemHealthStatus::Healthy, $metrics['status']);
    }

    public function test_queue_check_reports_job_counts(): void
    {
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->subMinutes(10)->timestamp,
            'created_at' => now()->subMinutes(10)->timestamp,
        ]);

        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Test failure',
            'failed_at' => now(),
        ]);

        $metrics = app(SystemHealthService::class)->queueMetrics();

        $this->assertSame(1, $metrics['pending_jobs']);
        $this->assertSame(1, $metrics['failed_jobs']);
        $this->assertSame(SystemHealthStatus::Critical, $metrics['status']);
    }

    public function test_storage_check_reports_disk_metrics(): void
    {
        $metrics = app(SystemHealthService::class)->storageMetrics();

        $this->assertArrayHasKey('used_label', $metrics);
        $this->assertArrayHasKey('free_label', $metrics);
        $this->assertArrayHasKey('uploads_label', $metrics);
        $this->assertArrayHasKey('backup_label', $metrics);
        $this->assertGreaterThanOrEqual(0, $metrics['usage_percent']);
        $this->assertContains($metrics['status'], [
            SystemHealthStatus::Healthy,
            SystemHealthStatus::Warning,
            SystemHealthStatus::Critical,
        ]);
    }

    public function test_permission_enforcement_blocks_view_without_rights(): void
    {
        $user = User::factory()->create([
            'company_id' => 1,
            'default_branch_id' => 1,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Designer');

        $this->actingAs($user)
            ->get(route('admin.operations.health.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.operations.health.snapshot'))
            ->assertForbidden();
    }

    public function test_manage_permission_required_for_refresh(): void
    {
        $viewer = $this->healthViewer();

        $this->actingAs($viewer)
            ->post(route('admin.operations.health.refresh'))
            ->assertForbidden();
    }

    public function test_authorized_admin_can_refresh_health_caches(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->post(route('admin.operations.health.refresh'))
            ->assertRedirect(route('admin.operations.health.index'))
            ->assertSessionHas('success');
    }

    public function test_system_operations_section_links_to_system_health(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'system-operations']))
            ->assertOk()
            ->assertSee(route('admin.operations.health.index'), false)
            ->assertSee(__('System Health'));
    }

    public function test_snapshot_endpoint_returns_json_payload(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->getJson(route('admin.operations.health.snapshot'))
            ->assertOk()
            ->assertJsonStructure([
                'generated_at',
                'system_status',
                'kpis',
                'database',
                'queue',
                'storage',
                'alerts',
            ]);
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

    protected function healthViewer(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Viewer');
        $user->givePermissionTo('operations.health.view');

        return $user;
    }
}
