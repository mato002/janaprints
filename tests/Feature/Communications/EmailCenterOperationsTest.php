<?php

namespace Tests\Feature\Communications;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\EmailAccountStatus;
use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Enums\IntegrationEmailProvider;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Jobs\Communications\SendEmailMessageJob;
use App\Models\Branch;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailAttachment;
use App\Models\Communications\EmailMessage;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Communications\DomainCommunicationChannelDispatcher;
use App\Support\Communications\Email\EmailDeliveryDiagnosticsService;
use App\Support\Communications\Email\EmailMessageService;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\SalesDocumentEmailService;
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
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EmailCenterOperationsTest extends TestCase
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
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(JanaPrintsTaxSeeder::class);

        Config::set('mailboxes.department.accounts', 'accounts@janaprints.co.ke');

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
        ]);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_invoice_email_creates_message_with_pdf_and_accounts_sender(): void
    {
        Queue::fake();

        $invoice = $this->postedInvoice();

        $this->actingAs($this->user)
            ->post(route('admin.invoices.email', $invoice))
            ->assertRedirect();

        $message = EmailMessage::query()->where('company_id', $this->company->id)->latest('id')->first();

        $this->assertNotNull($message);
        $this->assertSame(__('Invoice :number', ['number' => $invoice->invoice_number]), $message->subject);
        $this->assertSame('accounts@janaprints.co.ke', $message->account?->from_email);
        $this->assertDatabaseHas('email_attachments', [
            'email_message_id' => $message->id,
            'attachment_type' => 'invoice_pdf',
        ]);
        Queue::assertPushed(SendEmailMessageJob::class);
    }

    public function test_queued_email_can_be_cancelled(): void
    {
        $message = $this->createMessage(EmailDeliveryStatus::Queued);

        app(EmailMessageService::class)->cancel($message, $this->user->id);

        $message->refresh();
        $this->assertSame(EmailDeliveryStatus::Cancelled, $message->status);
        $this->assertDatabaseHas('email_delivery_events', [
            'email_message_id' => $message->id,
            'event' => 'cancelled',
        ]);
    }

    public function test_failed_email_can_be_retried(): void
    {
        Queue::fake();

        $message = $this->createMessage(EmailDeliveryStatus::Failed, [
            'failure_reason' => 'SMTP timeout',
            'failed_at' => now(),
            'provider_response' => ['metadata' => ['module' => 'sales'], 'retry_count' => 1],
        ]);

        app(EmailMessageService::class)->retry($message, $this->user->id);

        $message->refresh();
        $this->assertSame(EmailDeliveryStatus::Queued, $message->status);
        $this->assertSame(2, $message->provider_response['retry_count']);
        Queue::assertPushed(SendEmailMessageJob::class);
        $this->assertDatabaseHas('email_delivery_events', [
            'email_message_id' => $message->id,
            'event' => 'retry_requested',
        ]);
    }

    public function test_diagnostics_renders_on_settings_page(): void
    {
        $this->createIntegration();

        $response = $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.communications.email.settings'));

        $response->assertOk()
            ->assertSee(__('Delivery diagnostics'))
            ->assertSee(__('Recent failures'))
            ->assertSee(__('Recent success'));
    }

    public function test_diagnostics_service_returns_recent_lists(): void
    {
        $this->createIntegration();
        $this->createMessage(EmailDeliveryStatus::Failed, ['failure_reason' => 'Rejected']);
        $this->createMessage(EmailDeliveryStatus::Sent, ['sent_at' => now()]);

        $diagnostics = app(EmailDeliveryDiagnosticsService::class)->forCompany($this->company->id);

        $this->assertArrayHasKey('recent_failures', $diagnostics);
        $this->assertArrayHasKey('recent_successes', $diagnostics);
        $this->assertSame(__('Active'), $diagnostics['delivery_engine']['label']);
        $this->assertNotEmpty($diagnostics['recent_failures']);
        $this->assertNotEmpty($diagnostics['recent_successes']);
    }

    public function test_quotation_duplicate_protection_sends_single_customer_email(): void
    {
        Queue::fake();

        $quotation = Quotation::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'quotation_number' => 'QT-DUP-001',
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
            'prepared_by' => $this->user->id,
            'approved_by' => $this->user->id,
        ]);

        app(DomainCommunicationChannelDispatcher::class)->dispatch(
            \App\Enums\DomainCommunicationEvent::QuotationSent,
            $quotation,
            [
                'company_id' => $this->company->id,
                'customer_email' => $this->customer->email,
                'customer_phone' => '+254711222333',
                'customer_name' => $this->customer->company_name,
                'subject_label' => $quotation->quotation_number,
            ],
            $this->user,
        );

        app(SalesDocumentEmailService::class)->sendQuotation($quotation, $this->user);

        $customerEmails = EmailMessage::query()
            ->where('company_id', $this->company->id)
            ->get()
            ->filter(fn (EmailMessage $message) => collect($message->to_emails)->pluck('email')->contains($this->customer->email))
            ->count();

        $this->assertSame(1, $customerEmails);
    }

    public function test_journey_dispatcher_skips_quotation_sent_email_channel(): void
    {
        $quotation = Quotation::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'quotation_number' => 'QT-SKIP-001',
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
            'prepared_by' => $this->user->id,
        ]);

        $result = app(DomainCommunicationChannelDispatcher::class)->dispatch(
            \App\Enums\DomainCommunicationEvent::QuotationSent,
            $quotation,
            [
                'company_id' => $this->company->id,
                'customer_email' => $this->customer->email,
                'customer_phone' => '+254711222333',
                'customer_name' => $this->customer->company_name,
                'subject_label' => $quotation->quotation_number,
            ],
            $this->user,
        );

        $this->assertTrue($result['channels']['email']['skipped'] ?? false);
        $this->assertSame('document_email_handled', $result['channels']['email']['reason'] ?? null);
    }

    public function test_email_detail_drawer_endpoint_returns_metadata(): void
    {
        Permission::findOrCreate('communications.email.view');

        $message = $this->createMessage(EmailDeliveryStatus::Failed, [
            'failure_reason' => 'Connection refused',
            'failed_at' => now(),
            'provider_response' => [
                'metadata' => [
                    'module' => 'sales',
                    'entity_type' => 'customer_invoice',
                    'entity_id' => 1,
                    'document_number' => 'INV-001',
                ],
                'retry_count' => 2,
            ],
        ]);

        EmailAttachment::query()->create([
            'email_message_id' => $message->id,
            'attachment_type' => 'invoice_pdf',
            'label' => 'invoice.pdf',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('admin.communications.email.messages.show', $message));

        $response->assertOk()
            ->assertJsonPath('message.subject', $message->subject)
            ->assertJsonPath('message.failure_reason', 'Connection refused')
            ->assertJsonPath('message.retry_count', 2)
            ->assertJsonPath('message.module', 'sales')
            ->assertJsonPath('message.document_number', 'INV-001')
            ->assertJsonCount(1, 'message.attachments');
    }

    protected function postedInvoice(): CustomerInvoice
    {
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

        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($order, $this->user->id);
        $service->approve($invoice, $this->user->id);

        return $service->post($invoice->fresh(), $this->user->id);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createMessage(EmailDeliveryStatus $status, array $overrides = []): EmailMessage
    {
        $account = EmailAccount::query()->firstOrCreate(
            [
                'company_id' => $this->company->id,
                'from_email' => 'accounts@janaprints.co.ke',
            ],
            [
                'branch_id' => $this->branch->id,
                'name' => 'Accounts',
                'from_name' => 'Jana Accounts',
                'provider' => EmailProvider::Unconfigured,
                'status' => EmailAccountStatus::Active,
                'verification_status' => EmailVerificationStatus::Verified,
                'is_default' => true,
            ],
        );

        return EmailMessage::query()->create(array_merge([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'email_account_id' => $account->id,
            'to_emails' => [['email' => $this->customer->email, 'name' => $this->customer->company_name]],
            'subject' => 'Operations test',
            'body' => '<p>Test</p>',
            'status' => $status,
            'queued_at' => $status === EmailDeliveryStatus::Queued ? now() : null,
            'created_by' => $this->user->id,
        ], $overrides));
    }

    protected function createIntegration(): IntegrationEmailSetting
    {
        return IntegrationEmailSetting::query()->create([
            'company_id' => $this->company->id,
            'provider' => IntegrationEmailProvider::Smtp,
            'from_name' => 'Jana Prints',
            'from_email' => 'noreply@janaprints.test',
            'smtp_host' => 'smtp.test.local',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'smtp-user',
            'smtp_password' => 'smtp-pass',
            'is_active' => true,
        ]);
    }
}
