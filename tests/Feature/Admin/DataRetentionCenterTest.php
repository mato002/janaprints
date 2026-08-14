<?php

namespace Tests\Feature\Admin;

use App\Enums\RetentionPolicyDomain;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Operations\RetentionPolicy;
use App\Models\User;
use App\Services\Operations\DataRetentionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataRetentionCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_retention_dashboard_renders_default_policies(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.operations.retention.index'))
            ->assertOk()
            ->assertSee(__('Data Retention'))
            ->assertSee(__('Audit Logs'))
            ->assertSee(__('Activity Logs'))
            ->assertSee(__('Legal Hold'))
            ->assertSee(__('Retention Period'));

        $this->assertSame(
            count(RetentionPolicyDomain::cases()),
            RetentionPolicy::query()->where('company_id', $admin->company_id)->count(),
        );
    }

    public function test_service_seeds_all_retention_domains(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        app(DataRetentionService::class)->ensurePolicies($company->id);

        foreach (RetentionPolicyDomain::cases() as $domain) {
            $this->assertDatabaseHas('retention_policies', [
                'company_id' => $company->id,
                'domain' => $domain->value,
            ]);
        }
    }

    public function test_admin_can_update_retention_policy(): void
    {
        $admin = $this->companyAdmin();
        $policy = app(DataRetentionService::class)
            ->policiesForCompany($admin->company_id)
            ->firstWhere('domain', RetentionPolicyDomain::ActivityLogs);

        $this->actingAs($admin)
            ->put(route('admin.operations.retention.update', $policy), [
                'archive_after_days' => 60,
                'delete_after_days' => 400,
                'retention_period_days' => 400,
                'legal_hold' => 0,
            ])
            ->assertRedirect(route('admin.operations.retention.index'))
            ->assertSessionHas('success');

        $policy->refresh();
        $this->assertSame(60, $policy->archive_after_days);
        $this->assertSame(400, $policy->delete_after_days);
        $this->assertSame(400, $policy->retention_period_days);
        $this->assertSame($admin->id, $policy->updated_by);
    }

    public function test_legal_hold_requires_confirmation_to_release(): void
    {
        $admin = $this->companyAdmin();
        $policy = app(DataRetentionService::class)
            ->policiesForCompany($admin->company_id)
            ->firstWhere('domain', RetentionPolicyDomain::Documents);

        $policy->update(['legal_hold' => true]);

        $this->actingAs($admin)
            ->put(route('admin.operations.retention.update', $policy), [
                'archive_after_days' => 180,
                'delete_after_days' => 730,
                'retention_period_days' => 730,
                'legal_hold' => 0,
            ])
            ->assertSessionHasErrors('legal_hold');

        $policy->refresh();
        $this->assertTrue($policy->legal_hold);
    }

    public function test_admin_can_release_legal_hold_with_confirmation(): void
    {
        $admin = $this->companyAdmin();
        $policy = app(DataRetentionService::class)
            ->policiesForCompany($admin->company_id)
            ->firstWhere('domain', RetentionPolicyDomain::Communications);

        $policy->update(['legal_hold' => true]);

        $this->actingAs($admin)
            ->put(route('admin.operations.retention.update', $policy), [
                'archive_after_days' => 90,
                'delete_after_days' => 365,
                'retention_period_days' => 365,
                'legal_hold' => 0,
                'release_legal_hold' => 1,
            ])
            ->assertRedirect(route('admin.operations.retention.index'));

        $policy->refresh();
        $this->assertFalse($policy->legal_hold);
    }

    public function test_viewer_without_manage_permission_cannot_update_policy(): void
    {
        $viewer = $this->retentionViewer();
        $policy = app(DataRetentionService::class)
            ->policiesForCompany($viewer->company_id)
            ->first();

        $this->actingAs($viewer)
            ->put(route('admin.operations.retention.update', $policy), [
                'retention_period_days' => 100,
            ])
            ->assertForbidden();
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
            ->get(route('admin.operations.retention.index'))
            ->assertForbidden();
    }

    public function test_system_operations_section_links_to_data_retention(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.catalog', ['section' => 'operations']))
            ->assertOk()
            ->assertSee(__('Data Retention'))
            ->assertSee(route('admin.workspaces.administration.section', [
                'section' => 'operations',
                'tab' => 'data-retention',
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

    protected function retentionViewer(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('Viewer');
        $user->givePermissionTo('operations.retention.view');

        return $user;
    }
}
