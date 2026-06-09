<?php

namespace Tests\Feature\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\Quotation;
use App\Models\Sales\QuotationItem;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use App\Support\Commercial\CommercialCeoSalesIntelligencePresenter;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialCeoSalesIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_ceo_sales_intelligence_presenter_builds_all_sections(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'quotations.view', 'sales_orders.view', 'artwork.view', 'invoices.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Converted,
            'total_amount' => 50000,
        ]);

        QuotationItem::query()->create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationItemType::Service,
            'item_name' => 'Banner Print',
            'quantity' => 10,
            'unit_price' => 5000,
            'line_total' => 50000,
            'sort_order' => 1,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'created_by' => $user->id,
            'status' => SalesOrderStatus::ReadyForProduction,
            'total_amount' => 50000,
        ]);

        SalesOrderItem::query()->create([
            'sales_order_id' => $order->id,
            'item_name' => 'Banner Print',
            'quantity' => 10,
            'unit_price' => 5000,
            'line_total' => 50000,
            'sort_order' => 1,
        ]);

        Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::Rejected,
            'total_amount' => 10000,
        ]);

        ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'status' => ArtworkRequestStatus::Submitted,
            'due_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->bindTenant($company, $branch);
        $this->actingAs($user);

        $payload = app(CommercialCeoSalesIntelligencePresenter::class)->build();

        $this->assertNotNull($payload);
        $this->assertNotEmpty($payload['quote_conversion']['funnel']);
        $this->assertArrayHasKey('conversion_rate', $payload['quote_conversion']);
        $this->assertNotEmpty($payload['sales_performance']['top_staff']);
        $this->assertNotEmpty($payload['sales_performance']['top_customers']);
        $this->assertNotEmpty($payload['sales_performance']['top_products']);
        $this->assertNotEmpty($payload['sales_performance']['top_categories']);
        $this->assertCount(3, $payload['lost_business']['summary']);
        $this->assertArrayHasKey('delayed_jobs', $payload['artwork_impact']);
        $this->assertNotEmpty($payload['production_readiness']['items']);
        $this->assertCount(4, $payload['executive_summary']['items']);
    }

    public function test_commercial_hub_renders_ceo_sales_intelligence(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'quotations.view', 'sales_orders.view', 'artwork.view',
        ]);

        $this->bindTenant($company, $branch);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertSee(__('CEO Sales Intelligence'));
        $response->assertSee(__('Executive Summary'));
        $response->assertSee(__('Quote Conversion'));
        $response->assertSee(__('Sales Performance'));
        $response->assertSee(__('Lost Business Analysis'));
        $response->assertSee(__('Artwork Impact'));
        $response->assertSee(__('Production Readiness'));
        $response->assertSee(__('Commercial Health'));
    }

    protected function bindTenant(Company $company, Branch $branch): void
    {
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));
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
