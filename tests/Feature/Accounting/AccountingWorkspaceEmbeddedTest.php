<?php

namespace Tests\Feature\Accounting;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingWorkspaceEmbeddedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_accounting_hub_renders_dashboard_embedded_desk(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.workspaces.accounting'))
            ->assertOk()
            ->assertSee(route('admin.accounting.dashboard', ['embedded' => '1']), false);
    }

    public function test_accounting_dashboard_embedded_response_includes_workspace_frame(): void
    {
        $user = $this->companyAdmin();

        $this->actingAs($user)
            ->get(route('admin.accounting.dashboard', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('id="module-workspace-content"', false);
    }

    protected function companyAdmin(): User
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        return tap(User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]), fn (User $user) => $user->assignRole('Company Admin'));
    }
}
