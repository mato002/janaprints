<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\DomainCommunicationEvent;
use App\Enums\FulfilmentStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionOutputStatus;
use App\Enums\SalesOrderBillingType;
use App\Enums\SalesOrderFinancialStatus;
use App\Enums\SalesOrderStatus;
use App\Events\Communications\DomainCommunicationEventRaised;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\Sales\CustomerStatementService;
use App\Support\Sales\SalesOrderBillingEligibilityService;
use App\Support\Sales\SalesOrderFinancialStatusService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommercialBillingC8Test extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected SalesOrder $salesOrder;

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

        $this->salesOrder = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'billing_type' => SalesOrderBillingType::Deposit50,
            'payment_terms_days' => 30,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'created_by' => $this->user->id,
        ]);

        $this->salesOrder->items()->create([
            'item_name' => 'Flyers',
            'quantity' => 500,
            'unit_price' => 2,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_deposit_invoice_before_fulfilment(): void
    {
        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Deposit,
            'deposit_amount' => 580,
        ]);

        $this->assertSame(CustomerInvoiceType::Deposit, $invoice->invoice_type);
        app(CustomerInvoiceService::class)->approve($invoice, $this->user->id);
        app(CustomerInvoiceService::class)->post($invoice, $this->user->id);

        $this->salesOrder->refresh();
        $this->assertGreaterThan(0, (float) $this->salesOrder->deposit_invoiced_amount);
    }

    public function test_final_invoice_blocked_until_production_or_fulfilment(): void
    {
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'status' => ProductionJobCardStatus::ReadyForDispatch,
            'created_by' => $this->user->id,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder->fresh(), $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Standard,
        ]);
    }

    public function test_final_invoice_after_production_complete_without_delivery(): void
    {
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'status' => ProductionJobCardStatus::ReadyForDispatch,
            'created_by' => $this->user->id,
        ]);

        ProductionOutput::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'production_job_card_id' => $jobCard->id,
            'finished_inventory_item_id' => InventoryItem::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
            ])->id,
            'quantity_completed' => 500,
            'completion_status' => ProductionOutputStatus::Posted,
            'posted_job_marker' => $jobCard->id,
        ]);

        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder->fresh(), $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Standard,
        ]);

        $this->assertNotNull($invoice->id);
    }

    public function test_full_invoice_after_progress_draft_bills_balance(): void
    {
        app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Progress,
            'billing_percent' => 50,
        ]);

        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder->fresh(), $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Standard,
        ]);

        $this->assertEqualsWithDelta(580, (float) $invoice->total_amount, 0.01);
        $this->assertSame(__('Balance due'), $invoice->lines->first()->item_name);
    }

    public function test_full_invoice_after_posted_progress_bills_balance(): void
    {
        $service = app(CustomerInvoiceService::class);

        $progress = $service->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Progress,
            'billing_percent' => 50,
        ]);
        $service->approve($progress, $this->user->id);
        $service->post($progress, $this->user->id);

        $final = $service->createFromSalesOrder($this->salesOrder->fresh(), $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Standard,
        ]);

        $this->assertEqualsWithDelta(580, (float) $final->total_amount, 0.01);
        $this->assertSame(__('Balance due'), $final->lines->first()->item_name);
    }

    public function test_remaining_invoice_total_excludes_pending_drafts(): void
    {
        app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Progress,
            'billing_percent' => 50,
        ]);

        $this->assertEqualsWithDelta(580, $this->salesOrder->fresh()->remainingInvoiceTotal(), 0.01);
    }

    public function test_final_invoice_after_fulfilment(): void
    {
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'sales_order_id' => $this->salesOrder->id,
            'customer_id' => $this->customer->id,
            'status' => ProductionJobCardStatus::ReadyForDispatch,
            'created_by' => $this->user->id,
        ]);

        ProductionFulfilment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'sales_order_id' => $this->salesOrder->id,
            'production_job_card_id' => $jobCard->id,
            'status' => FulfilmentStatus::Collected,
            'invoice_ready' => true,
            'collected_at' => now(),
        ]);

        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder->fresh(), $this->user->id);
        $this->assertNotNull($invoice->id);
    }

    public function test_payment_allocation_updates_balance(): void
    {
        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Deposit,
            'deposit_amount' => 500,
        ]);
        $service->approve($invoice, $this->user->id);
        $service->post($invoice, $this->user->id);

        $invoice->refresh();

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'amount' => (float) $invoice->total_amount,
            'payment_method' => 'cash',
            'allocations' => [['customer_invoice_id' => $invoice->id, 'amount' => (float) $invoice->total_amount]],
        ]);

        app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $invoice->refresh();
        $this->assertEqualsWithDelta(0, (float) $invoice->balance_due, 0.01);
    }

    public function test_receivable_aging_and_financial_status(): void
    {
        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Deposit,
            'deposit_amount' => 400,
            'due_date' => now()->subDays(45)->toDateString(),
        ]);
        $service->approve($invoice, $this->user->id);
        $service->post($invoice, $this->user->id);

        $snapshot = app(SalesOrderFinancialStatusService::class)->snapshot($this->salesOrder->fresh());
        $this->assertContains($snapshot['financial_status'], [
            SalesOrderFinancialStatus::FullyInvoiced,
            SalesOrderFinancialStatus::PartiallyInvoiced,
            SalesOrderFinancialStatus::PartiallyPaid,
            SalesOrderFinancialStatus::NotInvoiced,
        ]);

        $profile = app(\App\Support\Sales\CustomerFinancialIntelligenceService::class)->profile($this->customer);
        $this->assertArrayHasKey('credit_control', $profile);
        $this->assertGreaterThan(0, $profile['outstanding']);
    }

    public function test_customer_statement_builds(): void
    {
        $statement = app(CustomerStatementService::class)->build([
            'customer_id' => $this->customer->id,
            'from_date' => now()->subMonth()->toDateString(),
            'to_date' => now()->toDateString(),
        ]);

        $this->assertSame($this->customer->id, $statement['customer_id']);
        $this->assertArrayHasKey('entries', $statement);
    }

    public function test_invoice_generated_notification_on_post(): void
    {
        Event::fake([DomainCommunicationEventRaised::class]);

        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Deposit,
            'deposit_amount' => 300,
        ]);
        $service->approve($invoice, $this->user->id);
        $service->post($invoice, $this->user->id);

        Event::assertDispatched(DomainCommunicationEventRaised::class, function (DomainCommunicationEventRaised $event) {
            return $event->event === DomainCommunicationEvent::InvoiceGenerated;
        });
    }

    public function test_tenant_isolation_on_invoices(): void
    {
        $companyB = Company::factory()->create();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);

        CustomerInvoice::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TENANT-A',
            'invoice_type' => CustomerInvoiceType::Standard,
            'invoice_date' => now(),
            'status' => CustomerInvoiceStatus::Draft,
            'subtotal' => 100,
            'tax_amount' => 16,
            'total_amount' => 116,
            'created_by' => $this->user->id,
        ]);

        app()->instance(TenantContext::class, new TenantContext($companyB, $branchB));

        $this->assertEquals(0, CustomerInvoice::query()->forTenant()->count());
    }

    public function test_branch_isolation_on_payments(): void
    {
        $branchB = Branch::factory()->create(['company_id' => $this->company->id]);

        CustomerPayment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'payment_number' => 'PAY-BR-A',
            'payment_date' => now(),
            'amount' => 100,
            'allocated_amount' => 0,
            'unallocated_amount' => 100,
            'payment_method' => 'cash',
            'status' => \App\Enums\CustomerPaymentStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $branchB));

        $this->assertEquals(0, CustomerPayment::query()->forTenant()->count());
    }
}
