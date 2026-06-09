<?php

namespace Tests\Feature\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CommercialHandoffAttentionLevel;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Commercial\CommercialHandoffIntelligencePresenter;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialHandoffIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_attention_score_uses_age_and_value(): void
    {
        $this->assertSame(
            CommercialHandoffAttentionLevel::Critical,
            CommercialHandoffAttentionLevel::fromAgeAndValue(21, 1),
        );

        $this->assertSame(
            CommercialHandoffAttentionLevel::High,
            CommercialHandoffAttentionLevel::fromAgeAndValue(14, 1),
        );

        $this->assertSame(
            CommercialHandoffAttentionLevel::Medium,
            CommercialHandoffAttentionLevel::fromAgeAndValue(3, 1),
        );

        $this->assertSame(
            CommercialHandoffAttentionLevel::Low,
            CommercialHandoffAttentionLevel::fromAgeAndValue(1, 100),
        );
    }

    public function test_handoff_intelligence_detects_blocked_quote_without_artwork(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'quotations.view', 'artwork.view', 'sales_orders.view', 'production.view', 'invoices.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::Accepted,
            'total_amount' => 75000,
            'updated_at' => now()->subDays(15),
        ]);

        $this->bindTenant($company, $branch);
        $this->actingAs($user);

        $payload = app(CommercialHandoffIntelligencePresenter::class)->build();
        $blockedQuotes = collect($payload['sections'])->firstWhere('key', 'blocked_quotes');

        $this->assertNotNull($blockedQuotes);
        $this->assertSame($quotation->quotation_number, $blockedQuotes['items'][0]['reference']);
        $this->assertSame(CommercialHandoffAttentionLevel::Critical->value, $blockedQuotes['items'][0]['attention_level']);
    }

    public function test_commercial_hub_renders_handoff_intelligence_sections(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'quotations.view',
            'artwork.view',
            'sales_orders.view',
            'production.view',
            'invoices.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => QuotationStatus::Accepted,
        ]);

        ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => ArtworkRequestStatus::Submitted,
            'updated_at' => now()->subDays(5),
        ]);

        SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::ReadyForProduction,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.workspaces.commercial'));

        $response->assertOk();
        $response->assertSee(__('Handoff Intelligence Center'), false);
        $response->assertSee(__('Blocked Quotes'), false);
        $response->assertSee(__('Blocked Artwork'), false);
        $response->assertSee(__('Blocked Production'), false);
        $response->assertSee(__('Critical'), false);
    }

    public function test_blocked_production_billing_and_cashflow_rules(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'sales_orders.view', 'production.view', 'invoices.view',
        ]);

        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $readyOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::ReadyForProduction,
            'total_amount' => 20000,
        ]);

        $billingOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Completed,
            'total_amount' => 30000,
        ]);

        $cashflowOrder = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'status' => SalesOrderStatus::Completed,
            'total_amount' => 15000,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'sales_order_id' => $billingOrder->id,
            'customer_id' => $customer->id,
            'status' => ProductionJobCardStatus::Completed,
        ]);

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $cashflowOrder->id,
            'invoice_number' => 'INV-HANDOFF-001',
            'invoice_type' => 'standard',
            'invoice_date' => now()->subDays(10)->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 30000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 30000,
            'amount_paid' => 0,
            'balance_due' => 30000,
            'created_by' => $user->id,
        ]);

        $this->bindTenant($company, $branch);
        $this->actingAs($user);

        $payload = app(CommercialHandoffIntelligencePresenter::class)->build();

        $blockedProduction = collect($payload['sections'])->firstWhere('key', 'blocked_production');
        $blockedBilling = collect($payload['sections'])->firstWhere('key', 'blocked_billing');
        $blockedCashflow = collect($payload['sections'])->firstWhere('key', 'blocked_cashflow');

        $this->assertNotNull($blockedProduction);
        $this->assertSame($readyOrder->order_number, $blockedProduction['items'][0]['reference']);

        $this->assertNotNull($blockedBilling);
        $this->assertSame($jobCard->job_card_number, $blockedBilling['items'][0]['reference']);

        $this->assertNotNull($blockedCashflow);
        $this->assertSame($invoice->invoice_number, $blockedCashflow['items'][0]['reference']);
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
