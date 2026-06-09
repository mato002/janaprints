<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\SalesOrderStatus;
use App\Mail\CustomerPaymentReceiptMail;
use App\Models\Branch;
use App\Models\Communications\SmsMessage;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerPaymentReceiptTest extends TestCase
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
            'email' => 'customer@example.com',
            'phone' => '+254712345678',
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
            'item_name' => 'Brochures',
            'quantity' => 500,
            'unit_price' => 2,
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

    public function test_receipt_number_assigned_on_post(): void
    {
        $payment = $this->postFullPayment();

        $this->assertNotNull($payment->receipt_number);
        $this->assertSame('RCP-'.$payment->payment_number, $payment->receipt_number);
    }

    public function test_receipt_build_contains_required_fields(): void
    {
        $payment = $this->postFullPayment();
        $receipt = app(CustomerPaymentReceiptService::class)->build($payment);

        $this->assertSame($payment->receipt_number, $receipt['receipt_number']);
        $this->assertSame($payment->payment_date->toDateString(), $receipt['payment_date']);
        $this->assertSame($this->customer->company_name, $receipt['customer_name']);
        $this->assertSame($payment->payment_method->label(), $receipt['payment_method']);
        $this->assertEqualsWithDelta((float) $payment->amount, $receipt['amount'], 0.01);
        $this->assertArrayHasKey('balance_remaining', $receipt);
        $this->assertCount(1, $receipt['invoices_settled']);
        $this->assertSame($this->invoice->invoice_number, $receipt['invoices_settled'][0]['invoice_number']);
    }

    public function test_receipt_print_view_renders(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee($payment->receipt_number)
            ->assertSee($this->customer->company_name)
            ->assertSee($this->invoice->invoice_number);
    }

    public function test_receipt_pdf_download(): void
    {
        $payment = $this->postFullPayment();

        $response = $this->actingAs($this->user)
            ->get(route('admin.payments.receipt.pdf', $payment));

        $response->assertOk();
        $this->assertStringContainsString('text/html', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString($payment->receipt_number, $response->streamedContent());
    }

    public function test_receipt_email_sent(): void
    {
        Mail::fake();

        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->post(route('admin.payments.receipt.email', $payment))
            ->assertRedirect();

        Mail::assertSent(CustomerPaymentReceiptMail::class, function (CustomerPaymentReceiptMail $mail) use ($payment) {
            return $mail->payment->is($payment)
                && $mail->receipt['receipt_number'] === $payment->receipt_number;
        });

        $this->assertNotNull($payment->fresh()->receipt_emailed_at);
    }

    public function test_receipt_sms_sends_link(): void
    {
        $payment = $this->postFullPayment();

        $this->actingAs($this->user)
            ->post(route('admin.payments.receipt.sms', $payment))
            ->assertRedirect();

        $this->assertDatabaseHas('sms_messages', [
            'company_id' => $this->company->id,
            'phone_number' => $this->customer->phone,
        ]);

        $message = SmsMessage::query()->where('phone_number', $this->customer->phone)->first();
        $this->assertStringContainsString($payment->receipt_number, $message->message_body);
        $this->assertNotNull($payment->fresh()->receipt_sms_sent_at);
    }

    public function test_signed_public_receipt_url_works(): void
    {
        $payment = $this->postFullPayment();
        $url = URL::temporarySignedRoute('public.payment-receipt.show', now()->addHour(), ['payment' => $payment->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee($payment->receipt_number);
    }

    public function test_receipt_requires_permission(): void
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

    public function test_draft_payment_cannot_generate_receipt(): void
    {
        $payment = app(CustomerPaymentService::class)->create($this->customer, $this->user->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => $this->invoice->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $this->invoice->id, 'amount' => $this->invoice->total_amount],
            ],
        ]);

        $this->assertSame(CustomerPaymentStatus::Draft, $payment->status);

        $this->actingAs($this->user)
            ->get(route('admin.payments.receipt', $payment))
            ->assertForbidden();
    }
}
