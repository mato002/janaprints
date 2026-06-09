<?php

namespace Tests\Feature\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_commercial_hub_renders_command_center_sections(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'quotations.view',
            'sales_orders.view',
            'artwork.view',
            'commercial.approvals.view',
            'invoices.view',
            'crm.customers.view',
            'commercial.reports.sales.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::PendingApproval,
        ]);

        SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::ReadyForProduction,
        ]);

        ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ArtworkRequestStatus::Submitted,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertSee(__('Commercial Command Center'), false);
        $response->assertSee(__('Open Quotations'), false);
        $response->assertSee(__('Commercial Pipeline'), false);
        $response->assertSee(__('Quick Attention Center'), false);
        $response->assertSee(__('Top Customers'), false);
        $response->assertSee(__('Quotes Awaiting Approval'), false);
        $response->assertSee(__('Approvals Queue'), false);
        $response->assertSee(__('CRM'), false);
    }

    public function test_command_center_quick_actions_are_permission_aware(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['quotations.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertDontSee(__('New Quotation'), false);
        $response->assertDontSee(__('Approvals Queue'), false);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions, ?Company $company = null, ?Branch $branch = null): array
    {
        $company ??= Company::factory()->create();
        $branch ??= Branch::factory()->create(['company_id' => $company->id]);

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
