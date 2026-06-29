<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\JournalStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Accounting\Journal;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\CustomerInvoiceService;
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

class CustomerInvoiceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

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

        $this->salesOrder = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'created_by' => $this->user->id,
        ]);

        $this->salesOrder->items()->create([
            'item_name' => 'Business cards',
            'quantity' => 1000,
            'unit_price' => 1,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_full_invoice_uses_sales_order_tax_rate_not_global_default(): void
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 5000,
            'tax_amount' => 150,
            'total_amount' => 5150,
            'created_by' => $this->user->id,
        ]);

        $order->items()->create([
            'item_name' => 'A5 Flyers',
            'quantity' => 10,
            'unit_price' => 500,
            'line_total' => 5000,
            'sort_order' => 1,
        ]);

        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($order, $this->user->id);

        $this->assertEqualsWithDelta(5150, (float) $invoice->total_amount, 0.01);
    }

    public function test_creates_invoice_from_sales_order_and_posts_to_ar(): void
    {
        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($this->salesOrder, $this->user->id);
        $service->approve($invoice, $this->user->id);
        $posted = $service->post($invoice->fresh(), $this->user->id);

        $this->assertSame(CustomerInvoiceStatus::Posted, $posted->status);
        $this->assertNotNull($posted->posted_journal_id);
        $this->assertDatabaseHas('journals', [
            'id' => $posted->posted_journal_id,
            'status' => JournalStatus::Posted->value,
            'posting_event' => 'invoice.posted',
            'source_type' => 'customer_invoice',
            'source_id' => $posted->id,
        ]);
        $this->assertEqualsWithDelta(1160, (float) $this->salesOrder->fresh()->invoiced_total, 0.01);
    }

    public function test_progress_billing_respects_remaining_balance(): void
    {
        $service = app(CustomerInvoiceService::class);

        $first = $service->createFromSalesOrder($this->salesOrder, $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Progress,
            'billing_percent' => 50,
        ]);
        $service->approve($first, $this->user->id);
        $service->post($first, $this->user->id);

        $second = $service->createFromSalesOrder($this->salesOrder->fresh(), $this->user->id, [
            'invoice_type' => CustomerInvoiceType::Progress,
            'billing_percent' => 50,
        ]);

        $this->assertLessThanOrEqual(1160, (float) $second->total_amount);
    }

    public function test_credit_note_reverses_invoiced_amount_on_post(): void
    {
        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($this->salesOrder, $this->user->id);
        $service->approve($invoice, $this->user->id);
        $service->post($invoice, $this->user->id);

        $credit = $service->createCreditNote($invoice->fresh(), $this->user->id, []);
        $service->approve($credit, $this->user->id);
        $service->post($credit, $this->user->id);

        $this->assertSame(CustomerInvoiceType::CreditNote, $credit->invoice_type);
        $this->assertEqualsWithDelta(0, (float) $this->salesOrder->fresh()->invoiced_total, 0.01);
        $this->assertSame(2, Journal::query()->where('source_type', 'customer_invoice')->count());
    }

    public function test_invoice_index_requires_permission(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer)->get(route('admin.invoices.index', ['embedded' => '1']))->assertForbidden();
    }

    public function test_generates_invoice_number(): void
    {
        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder, $this->user->id);

        $this->assertNotEmpty($invoice->invoice_number);
        $this->assertSame(CustomerInvoiceStatus::Approved, $invoice->status);
    }

    public function test_create_invoice_from_sales_order_page_is_not_redirected_to_workspace_desk(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.invoices.from-sales-order', $this->salesOrder))
            ->assertOk()
            ->assertSee(__('Create invoice'), false)
            ->assertSee($this->salesOrder->order_number, false);
    }

    public function test_store_from_sales_order_creates_approved_invoice(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.invoices.store-from-sales-order', $this->salesOrder), [
                'invoice_type' => 'standard',
                'invoice_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $invoice = CustomerInvoice::query()->where('sales_order_id', $this->salesOrder->id)->first();
        $this->assertNotNull($invoice);
        $this->assertSame(CustomerInvoiceStatus::Approved, $invoice->status);
        $this->assertGreaterThan(0, $invoice->lines()->count());
    }

    public function test_super_admin_invoice_workflow_shows_valid_actions_only(): void
    {
        $superAdmin = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($this->salesOrder, $this->user->id);

        $this->actingAs($superAdmin)
            ->get(route('admin.invoices.show', $invoice))
            ->assertOk()
            ->assertDontSee('>'.__('Approve').'</button>', false)
            ->assertSee(__('Post to AR'), false);

        $secondOrder = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 500,
            'tax_amount' => 80,
            'total_amount' => 580,
            'created_by' => $this->user->id,
        ]);
        $secondOrder->items()->create([
            'item_name' => 'Flyers',
            'quantity' => 100,
            'unit_price' => 5,
            'line_total' => 500,
            'sort_order' => 1,
        ]);

        $approvedInvoice = $service->createFromSalesOrder($secondOrder, $this->user->id);

        $this->actingAs($superAdmin)
            ->get(route('admin.invoices.show', $approvedInvoice))
            ->assertOk()
            ->assertSee(__('Post to AR'), false);

        $this->actingAs($superAdmin)
            ->post(route('admin.invoices.post', $approvedInvoice))
            ->assertRedirect();
    }
}
