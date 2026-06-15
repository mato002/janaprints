<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\CustomerStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\CustomerPaymentAllocation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Documents\Presenters\InvoiceDocumentPresenter;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->company = Company::factory()->create(['code' => 'JANA']);
        $this->branch = Branch::factory()->create(['company_id' => $this->company->id, 'code' => 'HQ']);
        $this->user = $this->accountantUser($this->company, $this->branch, ['invoices.view']);
        $this->customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'company_name' => 'Print Client Ltd',
            'status' => CustomerStatus::Active,
        ]);

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_invoice_document_page_loads(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number)
            ->assertSee('INVOICE');
    }

    public function test_invoice_pdf_download_works(): void
    {
        $invoice = $this->makeInvoice();

        $response = $this->actingAs($this->user)
            ->get(route('admin.invoices.document.pdf', $invoice));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->streamedContent());
    }

    public function test_missing_sales_order_does_not_break_template(): void
    {
        $invoice = $this->makeInvoice(['sales_order_id' => null]);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_missing_job_card_does_not_break_template(): void
    {
        $invoice = $this->makeInvoice(['production_job_card_id' => null]);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_missing_delivery_note_does_not_break_template(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_invoice_with_no_payments_shows_full_balance_due(): void
    {
        $invoice = $this->makeInvoice([
            'status' => CustomerInvoiceStatus::Posted,
            'total_amount' => 2500,
            'balance_due' => 2500,
            'amount_paid' => 0,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee('KES 2,500.00')
            ->assertSee('Balance Due');
    }

    public function test_invoice_with_payment_shows_amount_paid_and_remaining_balance(): void
    {
        $invoice = $this->makeInvoice([
            'status' => CustomerInvoiceStatus::Posted,
            'total_amount' => 2000,
            'balance_due' => 500,
            'amount_paid' => 1500,
        ]);

        $payment = CustomerPayment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'payment_number' => 'PAY-DOC-001',
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => 1500,
            'allocated_amount' => 1500,
            'unallocated_amount' => 0,
            'status' => CustomerPaymentStatus::Posted,
            'created_by' => $this->user->id,
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);

        CustomerPaymentAllocation::query()->create([
            'customer_payment_id' => $payment->id,
            'customer_invoice_id' => $invoice->id,
            'amount' => 1500,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee('KES 1,500.00')
            ->assertSee('KES 500.00');
    }

    public function test_paid_invoice_shows_paid_badge_and_zero_balance(): void
    {
        $invoice = $this->makeInvoice([
            'status' => CustomerInvoiceStatus::Posted,
            'total_amount' => 1000,
            'balance_due' => 0,
            'amount_paid' => 1000,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee('Paid')
            ->assertSee('KES 0.00');
    }

    public function test_overdue_invoice_shows_overdue_state(): void
    {
        $invoice = $this->makeInvoice([
            'status' => CustomerInvoiceStatus::Posted,
            'invoice_date' => now()->subDays(45)->toDateString(),
            'due_date' => now()->subDays(15)->toDateString(),
            'total_amount' => 900,
            'balance_due' => 900,
            'amount_paid' => 0,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee('Overdue');
    }

    public function test_unauthorized_user_cannot_access_invoice_document(): void
    {
        $companyB = Company::factory()->create(['code' => 'INV-B']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id]);
        $customerB = Customer::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'status' => CustomerStatus::Active,
        ]);

        $invoiceB = $this->makeInvoice([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => User::factory()->create()->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoiceB))
            ->assertForbidden();
    }

    public function test_commercial_references_are_omitted_from_printed_invoice(): void
    {
        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'order_number' => 'SO-DOC-001',
            'status' => SalesOrderStatus::Confirmed,
        ]);

        $artwork = ArtworkRequest::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'request_number' => 'ART-DOC-001',
            'title' => 'Brochure Layout',
        ]);

        $salesOrder->update(['artwork_request_id' => $artwork->id]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $this->customer->id,
            'job_card_number' => 'JC-DOC-001',
            'status' => ProductionJobCardStatus::Completed,
            'artwork_request_id' => $artwork->id,
        ]);

        DeliveryNote::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'delivery_note_number' => 'DN-DOC-001',
            'customer_id' => $this->customer->id,
            'sales_order_id' => $salesOrder->id,
            'production_job_card_id' => $jobCard->id,
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryNoteStatus::Delivered,
        ]);

        $invoice = $this->makeInvoice([
            'sales_order_id' => $salesOrder->id,
            'production_job_card_id' => $jobCard->id,
        ]);

        $document = app(InvoiceDocumentPresenter::class)->present($invoice->fresh([
            'salesOrder.artworkRequest',
            'jobCard',
        ]));

        $this->assertSame([], $document['meta']);
        $this->assertTrue($document['customer']['compact'] ?? false);

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee('Print Client Ltd')
            ->assertDontSee('SO-DOC-001')
            ->assertDontSee('JC-DOC-001')
            ->assertDontSee('DN-DOC-001')
            ->assertDontSee('ART-DOC-001')
            ->assertDontSee('In Production');
    }

    public function test_payment_details_render_from_config_safely(): void
    {
        Config::set('documents.payment', [
            'mpesa_paybill' => '522522',
            'mpesa_account' => 'JANA PRINTS',
            'cheque_payable_to' => 'Jana Prints Ltd',
            'bank_name' => 'KCB Bank',
            'bank_branch' => 'Westlands',
            'bank_account' => '1234567890',
            'bank_account_name' => 'Jana Prints Ltd',
        ]);

        $invoice = $this->makeInvoice();
        $document = app(InvoiceDocumentPresenter::class)->present($invoice);

        $this->assertNotEmpty($document['paymentFooter']);
        $this->assertTrue(
            collect($document['paymentFooter'])->contains(fn (string $line) => str_contains($line, '522522'))
        );

        $this->actingAs($this->user)
            ->get(route('admin.invoices.document', $invoice))
            ->assertOk()
            ->assertSee('522522')
            ->assertSee('KCB Bank');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeInvoice(array $overrides = []): CustomerInvoice
    {
        $invoice = CustomerInvoice::query()->create(array_merge([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-DOC-'.fake()->unique()->numerify('####'),
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'currency' => 'KES',
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'discount_amount' => 0,
            'total_amount' => 1160,
            'amount_paid' => 0,
            'balance_due' => 1160,
            'created_by' => $this->user->id,
        ], $overrides));

        $invoice->lines()->create([
            'item_name' => 'Business Cards',
            'description' => '350gsm matte',
            'quantity' => 500,
            'unit_price' => 2,
            'discount' => 0,
            'tax_rate' => 16,
            'line_subtotal' => 1000,
            'tax_amount' => 160,
            'line_total' => 1160,
            'sort_order' => 1,
        ]);

        return $invoice;
    }

    protected function accountantUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Accountant', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Accountant');

        return $user;
    }
}
