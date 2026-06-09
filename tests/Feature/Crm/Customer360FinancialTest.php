<?php

namespace Tests\Feature\Crm;

use App\Enums\CustomerPaymentMethod;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\CustomerFinancialIntelligenceService;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Customer360FinancialTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected CustomerInvoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(JanaPrintsTaxSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');

        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'created_by' => $this->user->id,
        ]);
        $order->items()->create([
            'item_name' => 'Flyers',
            'quantity' => 1000,
            'unit_price' => 1,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $invoiceService = app(CustomerInvoiceService::class);
        $this->invoice = $invoiceService->createFromSalesOrder($order, $this->user->id);
        $invoiceService->approve($this->invoice, $this->user->id);
        $invoiceService->post($this->invoice->fresh(), $this->user->id);
        $this->invoice = $this->invoice->fresh();

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    protected function postPayment(): void
    {
        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $this->invoice->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $this->invoice->total_amount],
            ],
        ]);

        app(CustomerPaymentService::class)->post($payment, $this->user->id);
    }

    public function test_financial_tab_renders_outstanding_balance(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', ['customer' => $this->customer, 'tab' => 'financial']))
            ->assertOk()
            ->assertSee(__('Financial'))
            ->assertSee(__('Outstanding balance'))
            ->assertSee($this->invoice->invoice_number)
            ->assertSee(number_format($this->invoice->total_amount, 2));
    }

    public function test_financial_intelligence_profile_uses_ledger_balance(): void
    {
        $profile = app(CustomerFinancialIntelligenceService::class)->profile($this->customer);

        $this->assertEqualsWithDelta((float) $this->invoice->total_amount, $profile['outstanding'], 0.01);
        $this->assertEqualsWithDelta((float) $this->invoice->total_amount, $profile['total_invoiced'], 0.01);
        $this->assertArrayHasKey('aging', $profile);
        $this->assertArrayHasKey('collection', $profile);
    }

    public function test_statement_section_renders_ledger_entries(): void
    {
        $from = now()->subMonth()->toDateString();
        $to = now()->toDateString();

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', [
                'customer' => $this->customer,
                'tab' => 'financial',
                'financial_section' => 'statement',
                'statement_from' => $from,
                'statement_to' => $to,
            ]))
            ->assertOk()
            ->assertSee(__('Statement'))
            ->assertSee($this->invoice->invoice_number)
            ->assertSee(__('Closing'));
    }

    public function test_payments_section_lists_posted_payment(): void
    {
        $this->postPayment();

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', [
                'customer' => $this->customer,
                'tab' => 'financial',
                'financial_section' => 'payments',
            ]))
            ->assertOk()
            ->assertSee(__('Payments'))
            ->assertSee('PAY');
    }

    public function test_financial_tab_hidden_without_permissions(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $viewer->givePermissionTo('crm.customers.view');

        $this->actingAs($viewer)
            ->get(route('admin.crm.customers.show', $this->customer))
            ->assertOk()
            ->assertDontSee(__('Financial'));
    }

    public function test_receipt_history_lists_issued_receipts(): void
    {
        $this->postPayment();

        $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', [
                'customer' => $this->customer,
                'tab' => 'financial',
                'financial_section' => 'receipts',
            ]))
            ->assertOk()
            ->assertSee(__('Receipt history'))
            ->assertSee('RCP-');
    }
}
