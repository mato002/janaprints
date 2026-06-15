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
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Documents\Presenters\ReceiptDocumentPresenter;
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
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReceiptDocumentTest extends TestCase
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
            'company_name' => 'Receipt Client Ltd',
            'email' => 'receipts@client.test',
            'phone' => '+254700000001',
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
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_admin_receipt_page_renders_professional_template(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('PAYMENT RECEIPT')
            ->assertSee($payment->receipt_number)
            ->assertSee('Invoice No')
            ->assertSee('Payment Details');
    }

    public function test_receipt_pdf_download_works(): void
    {
        $payment = $this->postFullPayment();

        $response = $this->actingAs($this->user)
            ->get(route('admin.payments.receipt.pdf', $payment));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    public function test_public_signed_receipt_page_renders(): void
    {
        $payment = $this->postFullPayment();
        $url = URL::temporarySignedRoute('public.payment-receipt.show', now()->addHour(), ['payment' => $payment->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee($payment->receipt_number)
            ->assertSee('PAYMENT RECEIPT');
    }

    public function test_unsigned_public_receipt_access_is_rejected(): void
    {
        $payment = $this->postFullPayment();

        $this->get(route('public.payment-receipt.show', $payment))
            ->assertForbidden();
    }

    public function test_draft_payment_receipt_access_is_blocked(): void
    {
        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $this->invoice->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $this->invoice->total_amount],
            ],
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertForbidden();
    }

    public function test_receipt_number_renders(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('RCP-'.$payment->payment_number);
    }

    public function test_amount_received_renders(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Amount Received')
            ->assertSee(number_format((float) $payment->amount, 2));
    }

    public function test_balance_before_and_after_render(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Balance Before')
            ->assertSee('Balance After');
    }

    public function test_invoice_allocation_table_renders(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Invoice No')
            ->assertSee($this->invoice->invoice_number)
            ->assertSee('Amount Applied');
    }

    public function test_payment_with_no_allocations_shows_safe_empty_state(): void
    {
        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => 500,
            'is_deposit' => true,
            'allocations' => [],
        ]);
        $payment = app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Payment has not been allocated to a specific invoice', false);
    }

    public function test_missing_customer_contact_fields_do_not_break_template(): void
    {
        $minimalCustomer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'company_name' => 'Minimal Receipt Client',
            'contact_person' => null,
            'phone' => null,
            'email' => null,
            'physical_address' => null,
            'status' => CustomerStatus::Active,
        ]);

        $payment = app(CustomerPaymentService::class)->create($minimalCustomer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => 300,
            'is_deposit' => true,
            'allocations' => [],
        ]);
        $payment = app(CustomerPaymentService::class)->post($payment, $this->user->id);

        $document = app(ReceiptDocumentPresenter::class)->present($payment);

        $this->assertSame('Minimal Receipt Client', $document['customer']['company']);

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Minimal Receipt Client');
    }

    public function test_unauthorized_admin_cannot_access_receipt(): void
    {
        $payment = $this->postFullPayment();

        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $viewer->givePermissionTo('payments.view');

        $this->actingAs($viewer)
            ->get(route('admin.payments.receipt', $payment))
            ->assertForbidden();
    }

    protected function postFullPayment(): \App\Models\Sales\CustomerPayment
    {
        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $this->invoice->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $this->invoice->total_amount],
            ],
        ]);

        return app(CustomerPaymentService::class)->post($payment, $this->user->id);
    }
}
