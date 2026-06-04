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

        $this->actingAs($viewer)->get(route('admin.invoices.index'))->assertForbidden();
    }

    public function test_generates_invoice_number(): void
    {
        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($this->salesOrder, $this->user->id);

        $this->assertNotEmpty($invoice->invoice_number);
        $this->assertSame(CustomerInvoiceStatus::Draft, $invoice->status);
    }
}
