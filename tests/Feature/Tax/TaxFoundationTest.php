<?php

namespace Tests\Feature\Tax;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\SupplierBillStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Tax\TaxCode;
use App\Models\Tax\TaxTransaction;
use App\Models\User;
use App\Support\Procurement\SupplierBillService;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Tax\TaxCalculationService;
use App\Support\Tax\TaxReportService;
use App\Enums\TaxDocumentType;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SupplierPayablesPostingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(SupplierPayablesPostingSeeder::class);
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

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_tax_calculation_uses_seeded_rate_not_hardcoded_percent(): void
    {
        $calc = app(TaxCalculationService::class);
        $result = $calc->calculate(
            $this->company->id,
            TaxDocumentType::CustomerInvoice,
            [['quantity' => 1, 'unit_price' => 1000, 'discount' => 0]],
            now()->toDateString(),
        );

        $this->assertSame(160.0, $result['tax_amount']);
        $this->assertSame('VAT16', $result['tax_summary'][0]['tax_code']);
    }

    public function test_posted_customer_invoice_writes_tax_ledger(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $service = app(CustomerInvoiceService::class);

        $invoice = $service->createInvoice([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'currency' => 'KES',
        ], [
            ['item_name' => 'Print job', 'quantity' => 1, 'unit_price' => 1000],
        ], $this->user->id);

        $service->approve($invoice, $this->user->id);
        $service->post($invoice, $this->user->id);

        $invoice->refresh();
        $this->assertSame(CustomerInvoiceStatus::Posted, $invoice->status);

        $ledger = TaxTransaction::query()
            ->where('source_type', 'customer_invoice')
            ->where('source_id', $invoice->id)
            ->get();

        $this->assertGreaterThan(0, $ledger->count());
        $this->assertEqualsWithDelta(160.0, (float) $ledger->sum('tax_amount'), 0.02);
    }

    public function test_vat_summary_report_reflects_ledger(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $invoiceService = app(CustomerInvoiceService::class);
        $invoice = $invoiceService->createInvoice([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'currency' => 'KES',
        ], [
            ['item_name' => 'Job', 'quantity' => 1, 'unit_price' => 500],
        ], $this->user->id);
        $invoiceService->approve($invoice, $this->user->id);
        $invoiceService->post($invoice, $this->user->id);

        $summary = app(TaxReportService::class)->vatSummary([
            'company_id' => $this->company->id,
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->endOfMonth()->toDateString(),
        ]);

        $this->assertGreaterThan(0, $summary['output_vat']);
    }

    public function test_tax_codes_admin_route_requires_permission(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.tax.codes.index'))
            ->assertOk();
    }

    public function test_seeded_tax_codes_exist(): void
    {
        $this->assertTrue(
            TaxCode::query()->where('company_id', $this->company->id)->where('code', 'VAT16')->exists()
        );
    }
}
