<?php

namespace Tests\Feature\Security;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Communications\NotificationService;
use App\Support\PublicHash\PublicHashGenerator;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerPaymentReceiptService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicHashExternalSurfaceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $admin;

    protected Customer $customer;

    protected User $clientUser;

    protected PublicHashGenerator $generator;

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
            'email' => 'client@example.com',
        ]);

        $this->clientUser = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'employee_id' => null,
        ]);

        session([
            'active_company_id' => $this->company->id,
            'active_branch_id' => $this->branch->id,
        ]);

        $this->generator = app(PublicHashGenerator::class);
    }

    public function test_client_quotation_url_uses_hash(): void
    {
        $quotation = $this->clientQuotation();

        $url = route('client.quotations.show', $quotation);

        $this->assertShowRouteUsesPublicHash($url, $quotation);

        $this->actingAsClient($this->clientUser)
            ->get($url)
            ->assertOk();
    }

    public function test_client_order_url_uses_hash(): void
    {
        $order = $this->clientOrder();

        $url = route('client.orders.show', $order);

        $this->assertShowRouteUsesPublicHash($url, $order);

        $this->actingAsClient($this->clientUser)
            ->get($url)
            ->assertOk();
    }

    public function test_client_invoice_url_uses_hash(): void
    {
        $invoice = $this->clientInvoiceFromOrder();

        $url = route('client.invoices.show', $invoice);

        $this->assertShowRouteUsesPublicHash($url, $invoice);

        $this->actingAsClient($this->clientUser)
            ->get($url)
            ->assertOk();
    }

    public function test_client_job_url_uses_hash(): void
    {
        $jobCard = $this->clientJobCard();

        $url = route('client.jobs.show', $jobCard);

        $this->assertShowRouteUsesPublicHash($url, $jobCard);

        $this->actingAsClient($this->clientUser)
            ->get($url)
            ->assertOk();
    }

    public function test_client_cannot_access_another_customers_resource_by_hash(): void
    {
        $otherCustomer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $otherUser = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'customer_id' => $otherCustomer->id,
            'employee_id' => null,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $otherCustomer->id,
            'prepared_by' => $this->admin->id,
            'status' => QuotationStatus::Sent,
        ]);

        $this->actingAsClient($this->clientUser)
            ->get(route('client.quotations.show', $quotation))
            ->assertNotFound();
    }

    public function test_client_numeric_url_rejected_when_fallback_disabled(): void
    {
        Config::set('public_hashes.numeric_fallback_enabled', false);

        $quotation = $this->clientQuotation();

        $this->actingAsClient($this->clientUser)
            ->get('/client/quotations/'.$quotation->id)
            ->assertNotFound();
    }

    public function test_new_public_receipt_signed_url_uses_payment_public_id(): void
    {
        $payment = $this->postedPayment();

        $url = app(CustomerPaymentReceiptService::class)->signedPublicUrl($payment);

        $this->assertStringContainsString('/payment-receipt/'.$payment->public_id, $url);
        $this->assertStringNotContainsString('/payment-receipt/'.$payment->id, strtok($url, '?'));
    }

    public function test_old_numeric_signed_receipt_url_still_works(): void
    {
        $payment = $this->postedPayment();

        $url = URL::temporarySignedRoute(
            'public.payment-receipt.show',
            now()->addHour(),
            ['payment' => $payment->id],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee($payment->receipt_number);
    }

    public function test_new_hash_signed_receipt_url_works_without_tenant_context(): void
    {
        $payment = $this->postedPayment();

        app()->instance(TenantContext::class, new TenantContext(null, null, false));

        $url = URL::temporarySignedRoute(
            'public.payment-receipt.show',
            now()->addHour(),
            ['payment' => $payment->public_id],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee($payment->receipt_number);
    }

    public function test_tampered_signed_receipt_url_fails(): void
    {
        $payment = $this->postedPayment();

        $url = app(CustomerPaymentReceiptService::class)->signedPublicUrl($payment);
        $tampered = preg_replace('/signature=[^&]+/', 'signature=invalid', $url);

        $this->get($tampered)->assertForbidden();
    }

    public function test_unsigned_public_receipt_url_is_rejected(): void
    {
        $payment = $this->postedPayment();

        $this->get('/payment-receipt/'.$payment->public_id)
            ->assertForbidden();
    }

    public function test_public_receipt_html_does_not_expose_numeric_payment_id(): void
    {
        $payment = $this->postedPayment();

        app()->instance(TenantContext::class, new TenantContext(null, null, false));

        $url = app(CustomerPaymentReceiptService::class)->signedPublicUrl($payment);

        $response = $this->get($url)->assertOk();

        $this->assertStringNotContainsString(
            'payment_id',
            $response->getContent(),
        );
        $this->assertStringNotContainsString(
            '>"'.$payment->id.'"<',
            $response->getContent(),
        );
    }

    public function test_receipt_email_public_url_uses_hash(): void
    {
        $payment = $this->postedPayment();

        $receipt = app(CustomerPaymentReceiptService::class)->build($payment);

        $this->assertStringContainsString($payment->public_id, $receipt['public_url']);
        $this->assertStringNotContainsString(
            '/payment-receipt/'.$payment->id,
            strtok($receipt['public_url'], '?'),
        );
    }

    public function test_ess_payslip_and_document_models_are_deferred_from_public_hash(): void
    {
        $exposed = config('public_hashes.route_exposed_models', []);

        $this->assertNotContains(\App\Models\Hr\PayrollPayslip::class, $exposed);
        $this->assertNotContains(\App\Models\Hr\EmployeeDocument::class, $exposed);
    }

    public function test_notification_action_urls_generated_after_migration_use_hashes(): void
    {
        $invoice = $this->clientInvoiceFromOrder();

        $notification = app(NotificationService::class)->create([
            'company_id' => $this->company->id,
            'recipient_user_id' => $this->admin->id,
            'type' => NotificationType::InvoiceGenerated,
            'title' => 'Invoice posted',
            'body' => 'Invoice ready.',
            'priority' => NotificationPriority::Normal,
            'subject_type' => CustomerInvoice::class,
            'subject_id' => $invoice->id,
            'created_by' => $this->admin->id,
        ]);

        $this->assertNotNull($notification);

        $actionUrl = app(NotificationService::class)->resolveActionUrl($notification);

        $this->assertNotNull($actionUrl);
        $this->assertStringContainsString($invoice->public_id, $actionUrl);
    }

    public function test_public_hash_audit_detects_external_numeric_url_leak(): void
    {
        $leakUrl = url('/client/invoices/12345');

        $this->assertMatchesRegularExpression('#/client/invoices/\d+#', parse_url($leakUrl, PHP_URL_PATH) ?? '');

        Artisan::call('public-hash:audit-stored-urls');

        $this->assertStringContainsString('report-only', strtolower(Artisan::output()));
    }

    protected function clientQuotation(): Quotation
    {
        return Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'prepared_by' => $this->admin->id,
            'status' => QuotationStatus::Sent,
        ]);
    }

    protected function clientOrder(): SalesOrder
    {
        return SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $this->admin->id,
        ]);
    }

    protected function clientJobCard(): ProductionJobCard
    {
        $order = $this->clientOrder();

        return ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_order_id' => $order->id,
            'status' => ProductionJobCardStatus::InProduction,
            'created_by' => $this->admin->id,
        ]);
    }

    protected function clientInvoiceFromOrder(): CustomerInvoice
    {
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
            'item_name' => 'Brochures',
            'quantity' => 500,
            'unit_price' => 2,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($order, $this->admin->id);
        app(CustomerInvoiceService::class)->approve($invoice, $this->admin->id);
        app(CustomerInvoiceService::class)->post($invoice->fresh(), $this->admin->id);

        return $invoice->fresh();
    }

    protected function clientInvoice(): CustomerInvoice
    {
        return CustomerInvoice::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-EXT-'.uniqid(),
            'invoice_type' => CustomerInvoiceType::Standard,
            'invoice_date' => now()->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'currency' => 'KES',
            'created_by' => $this->admin->id,
        ]);
    }

    protected function postedPayment(): CustomerPayment
    {
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
            'item_name' => 'Brochures',
            'quantity' => 500,
            'unit_price' => 2,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($order, $this->admin->id);
        app(CustomerInvoiceService::class)->approve($invoice, $this->admin->id);
        app(CustomerInvoiceService::class)->post($invoice->fresh(), $this->admin->id);

        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->admin->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $invoice->fresh()->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $invoice->id, 'amount' => $invoice->total_amount],
            ],
        ]);

        return app(CustomerPaymentService::class)->post($payment, $this->admin->id);
    }

    protected function actingAsClient(User $user)
    {
        return $this->withSession(['auth_context' => 'client'])->actingAs($user);
    }

    protected function assertShowRouteUsesPublicHash(string $url, Model $model): void
    {
        $this->assertStringContainsString((string) $model->public_id, $url);
        $this->assertDoesNotMatchRegularExpression(
            '#/'.preg_quote((string) $model->id, '#').'(?:\?|$)#',
            $url,
        );
    }
}
