<?php

namespace Tests\Feature\Communications;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\EmailAccountStatus;
use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Enums\IntegrationEmailProvider;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Jobs\Communications\SendEmailMessageJob;
use App\Models\Branch;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Communications\Email\CommunicationEntityLinkResolver;
use App\Support\Communications\Email\EmailAnalyticsService;
use App\Support\Communications\Email\EmailMessageService;
use App\Support\Communications\Email\EmailVisibilityService;
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
use Tests\TestCase;

class EmailCommunicationVisibilityTest extends TestCase
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
        Config::set('mailboxes.department.sales', 'sales@janaprints.co.ke');

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
            'email' => 'visibility@example.com',
        ]);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_customer_360_communications_tab_lists_invoice_email(): void
    {
        Queue::fake();

        $invoice = $this->postedInvoice();
        app(SalesDocumentEmailService::class)->sendInvoice($invoice, $this->user);

        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.crm.customers.show', ['customer' => $this->customer, 'tab' => 'communications']))
            ->assertOk()
            ->assertSee(__('Invoice emailed'))
            ->assertSee($invoice->invoice_number);
    }

    public function test_customer_email_history_can_be_filtered_by_receipts(): void
    {
        Queue::fake();

        $invoice = $this->postedInvoice();
        app(SalesDocumentEmailService::class)->sendInvoice($invoice, $this->user);

        $messages = app(EmailVisibilityService::class)->forCustomer($this->customer, 'receipts');

        $this->assertCount(0, $messages);

        $invoiceMessages = app(EmailVisibilityService::class)->forCustomer($this->customer, 'invoices');
        $this->assertCount(1, $invoiceMessages);
    }

    public function test_job_360_timeline_includes_related_quotation_email(): void
    {
        Queue::fake();

        $quotation = Quotation::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'quotation_number' => 'QT-VIS-001',
            'status' => \App\Enums\QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
            'prepared_by' => $this->user->id,
        ]);

        app(SalesDocumentEmailService::class)->sendQuotation($quotation, $this->user);

        $salesOrder = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'quotation_id' => $quotation->id,
            'created_by' => $this->user->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sales_order_id' => $salesOrder->id,
            'quotation_id' => $quotation->id,
            'job_card_number' => 'JOB-VIS-001',
            'status' => ProductionJobCardStatus::Queued,
            'created_by' => $this->user->id,
        ]);

        $communications = app(EmailVisibilityService::class)->forJobCard($jobCard);

        $this->assertCount(1, $communications);
        $this->assertSame('quotation', $communications->first()->provider_response['metadata']['entity_type']);
    }

    public function test_email_dashboard_returns_operational_metrics(): void
    {
        Queue::fake();
        app(SalesDocumentEmailService::class)->sendInvoice($this->postedInvoice(), $this->user);

        $stats = app(EmailAnalyticsService::class)->dashboard($this->company->id);

        $this->assertArrayHasKey('today', $stats);
        $this->assertArrayHasKey('month', $stats);
        $this->assertArrayHasKey('top_senders', $stats);
        $this->assertArrayHasKey('top_recipients', $stats);
        $this->assertArrayNotHasKey('open_rate', $stats);
        $this->assertSame(1, $stats['today']['queued']);
    }

    public function test_department_report_groups_accounts_mailbox(): void
    {
        Queue::fake();
        app(SalesDocumentEmailService::class)->sendInvoice($this->postedInvoice(), $this->user);

        $message = EmailMessage::query()->latest('id')->first();
        $message->update([
            'status' => EmailDeliveryStatus::Sent,
            'sent_at' => now(),
        ]);

        $report = app(EmailVisibilityService::class)->departmentReport($this->company->id);

        $this->assertSame(1, $report['accounts']['sent']);
        $this->assertSame(0, $report['sales']['sent']);
    }

    public function test_entity_link_resolver_builds_invoice_route(): void
    {
        $invoice = $this->postedInvoice();

        $link = app(CommunicationEntityLinkResolver::class)->resolve([
            'entity_type' => 'customer_invoice',
            'entity_id' => $invoice->id,
            'document_number' => $invoice->invoice_number,
        ]);

        $this->assertNotNull($link);
        $this->assertSame($invoice->invoice_number, $link['label']);
        $this->assertStringContainsString((string) $invoice->id, $link['url']);
    }

    public function test_email_detail_includes_related_entity_link(): void
    {
        $invoice = $this->postedInvoice();

        $message = $this->createMessage(EmailDeliveryStatus::Sent, [
            'subject' => 'Invoice test',
            'sent_at' => now(),
            'provider_response' => [
                'metadata' => [
                    'module' => 'sales',
                    'entity_type' => 'customer_invoice',
                    'entity_id' => $invoice->id,
                    'document_number' => $invoice->invoice_number,
                ],
            ],
        ]);

        $detail = app(EmailMessageService::class)->presentDetail($message);

        $this->assertNotNull($detail['related_entity']);
        $this->assertSame($invoice->invoice_number, $detail['related_entity']['label']);
    }

    public function test_communication_health_widget_renders_on_dashboard(): void
    {
        $this->createIntegration();

        $this->actingAs($this->user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('Communication Health'));
    }

    public function test_department_reports_page_renders(): void
    {
        $this->actingAs($this->user)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.communications.email.reports.index'))
            ->assertOk()
            ->assertSee(__('Department communication reports'))
            ->assertSee(__('Accounts'));
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
            'subject' => 'Visibility test',
            'body' => '<p>Test</p>',
            'status' => $status,
            'queued_at' => $status === EmailDeliveryStatus::Queued ? now() : null,
            'created_by' => $this->user->id,
        ], $overrides));
    }
}
