<?php

namespace Tests\Feature\Commercial;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialActivityCreatePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_activity_create_route_renders_form_not_show(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions(['commercial.activities.view', 'commercial.activities.create']);
        $user->assignRole('Sales');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.activities.create', ['from' => 'commercial']))
            ->assertOk()
            ->assertSee(__('Log activity'), false)
            ->assertSee('name="subject"', false);
    }

    public function test_activity_store_redirects_to_show_page(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions(['commercial.activities.view', 'commercial.activities.create']);
        $user->assignRole('Sales');

        $customer = \App\Models\Crm\Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('admin.commercial.activities.store'), [
            'customer_id' => $customer->id,
            'activity_type' => 'call',
            'status' => 'completed',
            'subject' => 'Discovery call',
            'activity_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $activity = \App\Models\Crm\CustomerActivity::query()->where('subject', 'Discovery call')->first();
        $response->assertRedirect(route('admin.commercial.activities.show', $activity));

        $this->actingAs($user)
            ->get(route('admin.commercial.activities.show', $activity))
            ->assertOk()
            ->assertSee('Discovery call', false);
    }

    public function test_activities_create_path_does_not_match_show_route(): void
    {
        $matched = app('router')->getRoutes()->match(
            \Illuminate\Http\Request::create('/admin/commercial/activities/create', 'GET')
        );

        $this->assertSame('admin.commercial.activities.create', $matched->getName());
    }
}
