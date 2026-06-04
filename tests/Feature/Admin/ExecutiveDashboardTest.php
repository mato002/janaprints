<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Dashboard\ExecutiveDashboardPresenter;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_command_center_renders_all_major_sections(): void
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

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('Executive Command Center'))
            ->assertSee(__('Attention Center'))
            ->assertSee(__('Production Pipeline'))
            ->assertSee(__('Branch Performance'))
            ->assertSee(__('Smart Insights'))
            ->assertSee(__('Quick Actions'))
            ->assertSee(__('Recent Activity'));
    }

    public function test_presenter_returns_dashboard_360_structure(): void
    {
        $payload = app(ExecutiveDashboardPresenter::class)->build();

        foreach ([
            'kpi_strip',
            'pipeline',
            'attention',
            'today_ops',
            'branches',
            'top_customers',
            'sales',
            'production',
            'inventory',
            'finance',
            'crm',
            'hr',
            'activity',
            'quick_actions',
            'insights',
        ] as $key) {
            $this->assertArrayHasKey($key, $payload, "Missing dashboard key: {$key}");
        }

        $this->assertCount(8, $payload['kpi_strip']);
        $this->assertGreaterThanOrEqual(7, count($payload['pipeline']));
    }
}
