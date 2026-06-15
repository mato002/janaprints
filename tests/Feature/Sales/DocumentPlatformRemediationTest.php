<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\CustomerStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
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
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentPlatformRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $admin;

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
        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole('Company Admin');

        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'created_by' => $this->admin->id,
        ]);
        $order->items()->create([
            'item_name' => 'Cards',
            'quantity' => 100,
            'unit_price' => 10,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $invoiceService = app(CustomerInvoiceService::class);
        $this->invoice = $invoiceService->createFromSalesOrder($order, $this->admin->id);
        $invoiceService->approve($this->invoice, $this->admin->id);
        $invoiceService->post($this->invoice->fresh(), $this->admin->id);
        $this->invoice = $this->invoice->fresh();

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_quotation_show_page_displays_download_pdf_link(): void
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'prepared_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.quotations.show', $quotation))
            ->assertOk()
            ->assertSee('View document', false)
            ->assertSee('Download PDF', false);
    }

    public function test_quotation_pdf_route_remains_protected(): void
    {
        $companyB = Company::factory()->create(['code' => 'REM-B']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $customerB = Customer::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'status' => CustomerStatus::Active,
        ]);

        $quotationB = Quotation::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'prepared_by' => User::factory()->create()->id,
        ]);

        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions(['quotations.view']);
        $viewer->assignRole('Sales');

        $this->actingAs($viewer)
            ->get(route('admin.quotations.document.pdf', $quotationB))
            ->assertForbidden();
    }

    public function test_unauthorized_users_do_not_see_receipt_links_on_invoice_show(): void
    {
        $payment = $this->postFullPayment();

        $salesUser = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions(['invoices.view', 'payments.view']);
        $salesUser->assignRole('Sales');

        $this->actingAs($salesUser)
            ->get(route('admin.invoices.show', $this->invoice))
            ->assertOk()
            ->assertSee($payment->payment_number)
            ->assertDontSee('View receipt', false);
    }

    public function test_authorized_users_see_receipt_links_on_invoice_show(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->admin)
            ->get(route('admin.invoices.show', $this->invoice))
            ->assertOk()
            ->assertSee($payment->payment_number)
            ->assertSee('View receipt', false);
    }

    public function test_document_payment_config_values_are_readable_from_config(): void
    {
        Config::set('documents.payment', [
            'mpesa_paybill' => '880100',
            'mpesa_account' => '559888',
            'cheque_payable_to' => 'JANA PRINTS',
            'bank_name' => 'NCBA Bank',
            'bank_branch' => 'Nakuru Branch',
            'bank_account' => '1001798601',
            'bank_account_name' => 'Jana Prints',
        ]);

        $payment = config('documents.payment');

        $this->assertSame('880100', $payment['mpesa_paybill']);
        $this->assertSame('559888', $payment['mpesa_account']);
        $this->assertSame('JANA PRINTS', $payment['cheque_payable_to']);
        $this->assertSame('NCBA Bank', $payment['bank_name']);
        $this->assertSame('Nakuru Branch', $payment['bank_branch']);
        $this->assertSame('1001798601', $payment['bank_account']);
        $this->assertSame('Jana Prints', $payment['bank_account_name']);
    }

    public function test_document_config_reads_bank_account_number_env_key(): void
    {
        Config::set('documents.payment.bank_account', '1001798601');

        $this->assertSame('1001798601', config('documents.payment.bank_account'));
    }

    public function test_legacy_receipt_views_are_removed(): void
    {
        $legacyPaths = [
            'views/admin/sales/payments/receipt.blade.php',
            'views/admin/sales/payments/receipt-pdf.blade.php',
            'views/admin/sales/payments/partials/receipt-body.blade.php',
            'views/admin/sales/payments/partials/receipt-content.blade.php',
            'views/public/payment-receipt.blade.php',
        ];

        foreach ($legacyPaths as $path) {
            $this->assertFileDoesNotExist(resource_path($path));
        }
    }

    public function test_document_styles_include_page_break_protection(): void
    {
        $styles = file_get_contents(resource_path('views/documents/partials/styles.blade.php'));

        $this->assertStringContainsString('page-break-inside: avoid', $styles);
        $this->assertStringContainsString('jp-doc__summary', $styles);
        $this->assertStringContainsString('jp-doc__payment-footer', $styles);
    }

    public function test_document_print_styles_hide_admin_shell_and_match_pdf_layout(): void
    {
        $printStyles = file_get_contents(resource_path('views/documents/partials/print-styles.blade.php'));

        $this->assertStringContainsString('@media print', $printStyles);
        $this->assertStringContainsString('visibility: hidden', $printStyles);
        $this->assertStringContainsString('#invoice-document', $printStyles);
        $this->assertStringContainsString('padding: 0 6mm 36mm', $printStyles);
        $this->assertStringContainsString('position: fixed', $printStyles);

        foreach (['invoice/show.blade.php', 'quotation/show.blade.php', 'receipt/show.blade.php'] as $view) {
            $show = file_get_contents(resource_path('views/documents/'.$view));
            $this->assertStringContainsString("documents.partials.print-styles", $show);
        }
    }

    public function test_document_cms_schema_covers_env_backed_settings(): void
    {
        $schema = config('document_cms.settings', []);

        foreach ([
            'company.name',
            'company.address',
            'payment.mpesa_paybill',
            'payment.bank_account',
            'terms.invoice',
            'footer.thanks',
            'labels.tax',
        ] as $key) {
            $this->assertArrayHasKey($key, $schema, "Missing document CMS key: {$key}");
        }
    }

    protected function postFullPayment(): \App\Models\Sales\CustomerPayment
    {
        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->admin->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $this->invoice->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $this->invoice->total_amount],
            ],
        ]);

        return app(CustomerPaymentService::class)->post($payment, $this->admin->id);
    }
}
