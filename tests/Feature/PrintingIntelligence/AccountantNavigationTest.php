<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountantNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_accountant_sees_printing_intelligence_sidebar_and_workspace(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $accountant = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $accountant->assignRole('Accountant');

        $this->actingAs($accountant);

        $this->assertTrue(
            app(\App\Support\Navigation\WorkspacePresenter::class)->isVisible('printing-intelligence'),
        );

        $this->actingAs($accountant)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('Printing Intelligence'), false)
            ->assertSee(route('admin.workspaces.printing-intelligence'), false);

        $this->actingAs($accountant)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.estimate-vs-actual'))
            ->assertOk()
            ->assertSee(__('Estimate vs Actual'));

        $this->actingAs($accountant)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.executive-intelligence'))
            ->assertOk()
            ->assertSee(__('Executive Intelligence'));
    }
}
