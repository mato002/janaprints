<?php

namespace Tests\Feature\Operations;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_queue_diagnostics_render_on_background_jobs_page(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.operations.jobs.index', ['embedded' => 1]))
            ->assertOk()
            ->assertSee(__('Queue Readiness'), false)
            ->assertSee('queue:work', false)
            ->assertDontSee('APP_KEY', false);
    }

    public function test_queue_readiness_service_lists_required_queues(): void
    {
        $diagnostics = app(\App\Services\Operations\QueueReadinessService::class)->diagnostics();

        $this->assertContains('emails', $diagnostics['queues']);
        $this->assertContains('exports', $diagnostics['queues']);
        $this->assertArrayHasKey('worker_commands', $diagnostics);
        $this->assertNotEmpty($diagnostics['worker_commands']);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
