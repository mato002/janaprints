<?php

namespace Tests\Feature\Communications;

use App\Enums\EmailAccountStatus;
use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Enums\IntegrationEmailProvider;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailAttachment;
use App\Models\Communications\EmailMessage;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Communications\Email\EmailAttachmentIntegrityInspector;
use App\Support\Communications\Email\EmailCommunicationCertificationService;
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
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailCommunicationCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $admin;

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

        Config::set('communications.retention_days', 3650);
        Config::set('mailboxes.department.accounts', 'accounts@janaprints.co.ke');

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
            'email' => 'cert@example.com',
        ]);

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
    }

    public function test_accountant_can_view_email_center_but_not_settings(): void
    {
        $accountant = $this->userWithRole('Accountant');

        $this->actingAs($accountant)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.communications.email.dashboard'))
            ->assertOk();

        $this->actingAs($accountant)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.communications.email.settings'))
            ->assertForbidden();
    }

    public function test_sales_cannot_access_certification_report(): void
    {
        $sales = $this->userWithRole('Sales');

        $this->actingAs($sales)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.communications.email.certification'))
            ->assertForbidden();
    }

    public function test_compose_syncs_communication_log(): void
    {
        Queue::fake();

        $message = app(EmailMessageService::class)->compose(
            $this->company->id,
            $this->admin->id,
            [
                'to' => [['email' => $this->customer->email]],
                'subject' => 'Audit compose test',
                'body' => '<p>Test</p>',
            ],
            sendNow: true,
        );

        $this->assertDatabaseHas('communication_logs', [
            'source_type' => EmailMessage::class,
            'source_id' => $message->id,
            'company_id' => $this->company->id,
        ]);

        $log = CommunicationLog::query()
            ->where('source_type', EmailMessage::class)
            ->where('source_id', $message->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertDatabaseHas('communication_delivery_events', [
            'communication_log_id' => $log->id,
            'event' => 'created',
        ]);
    }

    public function test_retry_syncs_communication_log_with_actor(): void
    {
        Queue::fake();

        $message = $this->createMessage(EmailDeliveryStatus::Failed, [
            'failure_reason' => 'Timeout',
            'failed_at' => now(),
        ]);

        app(EmailMessageService::class)->retry($message, $this->admin->id);

        $log = CommunicationLog::query()
            ->where('source_type', EmailMessage::class)
            ->where('source_id', $message->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertDatabaseHas('communication_delivery_events', [
            'communication_log_id' => $log->id,
            'event' => 'retry_requested',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_diagnostics_includes_retention_and_queue_metrics(): void
    {
        $this->createIntegration();

        $diagnostics = app(EmailDeliveryDiagnosticsService::class)->forCompany($this->company->id);

        $this->assertSame(3650, $diagnostics['retention']['days']);
        $this->assertFalse($diagnostics['retention']['auto_delete']);
        $this->assertArrayHasKey('depth', $diagnostics['queue']);
        $this->assertArrayHasKey('stuck_sending', $diagnostics['queue']);
        $this->assertArrayHasKey('queued_count', $diagnostics['queue']);
        $this->assertArrayHasKey('cancelled_count', $diagnostics['queue']);
    }

    public function test_attachment_integrity_command_reports_healthy(): void
    {
        Storage::fake('local');
        Config::set('communications.email_attachment_disk', 'local');

        $message = $this->createMessage(EmailDeliveryStatus::Sent);
        $path = 'email-attachments/2026/06/test.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        EmailAttachment::query()->create([
            'email_message_id' => $message->id,
            'attachment_type' => 'invoice_pdf',
            'label' => 'invoice.pdf',
            'file_path' => $path,
        ]);

        $report = app(EmailAttachmentIntegrityInspector::class)->inspect($this->company->id);

        $this->assertTrue($report['healthy']);
        $this->assertSame(0, $report['missing_files']);

        $this->artisan('communications:inspect-attachments', ['--company' => $this->company->id])
            ->assertSuccessful();
    }

    public function test_attachment_integrity_detects_missing_file(): void
    {
        Storage::fake('local');
        Config::set('communications.email_attachment_disk', 'local');

        $message = $this->createMessage(EmailDeliveryStatus::Sent);

        EmailAttachment::query()->create([
            'email_message_id' => $message->id,
            'attachment_type' => 'invoice_pdf',
            'label' => 'invoice.pdf',
            'file_path' => 'email-attachments/missing.pdf',
        ]);

        $report = app(EmailAttachmentIntegrityInspector::class)->inspect($this->company->id);

        $this->assertFalse($report['healthy']);
        $this->assertSame(1, $report['missing_files']);
    }

    public function test_certification_report_page_renders_for_admin(): void
    {
        $this->createIntegration();

        $this->actingAs($this->admin)
            ->withHeader('Turbo-Frame', 'module-workspace-content')
            ->get(route('admin.communications.email.certification'))
            ->assertOk()
            ->assertSee(__('Communications Certification Report'))
            ->assertSee(__('Readiness score'));
    }

    public function test_certification_service_produces_score(): void
    {
        $this->createIntegration();
        $this->createEmailAccount();

        $report = app(EmailCommunicationCertificationService::class)->report($this->company->id);

        $this->assertArrayHasKey('readiness_score', $report);
        $this->assertArrayHasKey('checks', $report);
        $this->assertArrayHasKey('smtp', $report);
        $this->assertArrayHasKey('queue', $report);
        $this->assertGreaterThanOrEqual(0, $report['readiness_score']);
    }

    public function test_invoice_email_creates_attachment_linked_to_invoice(): void
    {
        Queue::fake();

        $invoice = $this->postedInvoice();
        app(SalesDocumentEmailService::class)->sendInvoice($invoice, $this->admin);

        $message = EmailMessage::query()->where('company_id', $this->company->id)->latest('id')->first();
        $this->assertNotNull($message);

        $attachment = EmailAttachment::query()->where('email_message_id', $message->id)->first();

        $this->assertNotNull($attachment);
        $this->assertSame('invoice_pdf', $attachment->attachment_type->value ?? $attachment->attachment_type);
        $this->assertSame(CustomerInvoice::class, $attachment->attachable_type);
        $this->assertSame($invoice->id, $attachment->attachable_id);
    }

    protected function userWithRole(string $roleName): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName($roleName));

        return $user;
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
            'created_by' => $this->admin->id,
        ]);
        $order->items()->create([
            'item_name' => 'Brochures',
            'quantity' => 500,
            'unit_price' => 2,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $service = app(CustomerInvoiceService::class);
        $invoice = $service->createFromSalesOrder($order, $this->admin->id);
        $service->approve($invoice, $this->admin->id);

        return $service->post($invoice->fresh(), $this->admin->id);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createMessage(EmailDeliveryStatus $status, array $overrides = []): EmailMessage
    {
        $account = $this->createEmailAccount();

        return EmailMessage::query()->create(array_merge([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'email_account_id' => $account->id,
            'to_emails' => [['email' => $this->customer->email]],
            'subject' => 'Certification test',
            'body' => '<p>Test</p>',
            'status' => $status,
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    protected function createEmailAccount(): EmailAccount
    {
        return EmailAccount::query()->firstOrCreate(
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
