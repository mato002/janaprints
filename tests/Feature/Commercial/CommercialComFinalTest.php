<?php

namespace Tests\Feature\Commercial;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialComFinalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_sales_and_customer_service_sections_activate_com_final_cards(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.price_books.view',
            'commercial.approvals.view',
            'commercial.complaints.view',
            'commercial.tickets.view',
            'crm.customers.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $sales = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'sales']));
        $sales->assertOk();
        $sales->assertSee(route('admin.commercial.price-books.index'), false);
        $sales->assertSee(route('admin.commercial.approvals.index'), false);
        $sales->assertDontSee(__('Coming Soon'), false);

        $service = $this->actingAs($user)->get(route('admin.workspaces.commercial.section', ['section' => 'customer-service']));
        $service->assertOk();
        $service->assertSee(route('admin.commercial.complaints.index'), false);
        $service->assertSee(route('admin.commercial.support-tickets.index'), false);
        $service->assertDontSee(__('Customer 360'), false);
        $service->assertDontSee(__('Coming Soon'), false);
    }

    public function test_legacy_commercial_reports_route_shows_hub_links(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'reports.view',
            'commercial.reports.sales.view',
            'intelligence.commercial.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.reports.commercial'));

        $response->assertOk();
        $response->assertSee(__('Commercial 360'), false);
        $response->assertSee(route('admin.commercial.reports.sales.index'), false);
        $response->assertDontSee(__('Placeholder — module not connected yet'), false);
    }

    public function test_active_card_routes_do_not_404(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.price_books.view',
            'commercial.approvals.view',
            'commercial.complaints.view',
            'commercial.tickets.view',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        foreach ([
            'admin.commercial.price-books.index',
            'admin.commercial.approvals.index',
            'admin.commercial.complaints.index',
            'admin.commercial.support-tickets.index',
        ] as $routeName) {
            $this->actingAs($user)->get(route($routeName))->assertOk();
        }
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
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
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return [$company, $branch, $user];
    }
}
