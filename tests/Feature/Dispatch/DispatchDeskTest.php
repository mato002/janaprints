<?php

namespace Tests\Feature\Dispatch;

use App\Enums\ProductionJobCardStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DispatchDeskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_dispatch_desk_loads_with_ready_jobs_table(): void
    {
        [$user, $job] = $this->dispatchContext();

        $job->update(['status' => ProductionJobCardStatus::ReadyForDispatch]);

        $this->actingAs($user)
            ->get(route('admin.dispatch.dashboard', ['embedded' => '1']))
            ->assertOk()
            ->assertSee(__('Dispatch Desk'), false)
            ->assertSee(__('Jobs ready for dispatch'), false)
            ->assertSee($job->job_card_number, false);
    }

    public function test_production_dispatch_section_links_to_dispatch_desk(): void
    {
        [$user] = $this->dispatchContext();

        $this->actingAs($user)
            ->get(route('admin.workspaces.production.section', ['section' => 'dispatch']))
            ->assertOk()
            ->assertSee(__('Dispatch Desk'), false)
            ->assertSee(route('admin.dispatch.dashboard', ['embedded' => '1']), false)
            ->assertDontSee(route('admin.workspaces.dispatch'), false);
    }

    /**
     * @return array{0: User, 1: ProductionJobCard}
     */
    protected function dispatchContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions(['dispatch.view', 'dispatch.create', 'production.view']);
        $user->assignRole('Production');

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$user, $job];
    }
}
