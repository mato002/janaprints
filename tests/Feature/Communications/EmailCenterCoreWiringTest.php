<?php

namespace Tests\Feature\Communications;

use App\Enums\CommunicationLogChannel;
use App\Enums\EmailAccountStatus;
use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Enums\IntegrationEmailProvider;
use App\Jobs\Communications\SendEmailMessageJob;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Models\Company;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\User;
use App\Services\EmailIdentity\EmailSenderResolver;
use App\Support\Communications\Email\CorporateMailAuditService;
use App\Support\Communications\Email\EmailMessageService;
use App\Support\Communications\Email\EmailProviderGateway;
use App\Support\Communications\Email\Providers\IntegrationBridgedEmailProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailCenterCoreWiringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_email_provider_gateway_uses_integration_bridge(): void
    {
        $gateway = app(EmailProviderGateway::class);

        $reflection = new \ReflectionClass($gateway);
        $property = $reflection->getProperty('provider');
        $provider = $property->getValue($gateway);

        $this->assertInstanceOf(IntegrationBridgedEmailProvider::class, $provider);
    }

    public function test_compose_queues_send_email_message_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantContext();

        $service = app(EmailMessageService::class);
        $message = $service->compose($company->id, $user->id, [
            'to' => [['email' => 'customer@example.com', 'name' => 'Customer']],
            'subject' => 'Queued test',
            'body' => '<p>Hello</p>',
        ]);

        $this->assertSame(EmailDeliveryStatus::Queued, $message->status);
        Queue::assertPushed(SendEmailMessageJob::class, fn (SendEmailMessageJob $job) => $job->messageId === $message->id);
    }

    public function test_successful_send_populates_sent_at_and_communication_log(): void
    {
        Mail::fake();

        [$company, $branch, $user] = $this->tenantContext();

        $this->createActiveIntegration($company);

        $message = app(EmailMessageService::class)->compose($company->id, $user->id, [
            'to' => [['email' => 'customer@example.com', 'name' => 'Customer']],
            'subject' => 'Success test',
            'body' => '<p>Delivered</p>',
        ]);

        $message->refresh();

        $this->assertSame(EmailDeliveryStatus::Sent, $message->status);
        $this->assertNotNull($message->sent_at);
        $this->assertDatabaseHas('communication_logs', [
            'company_id' => $company->id,
            'channel' => CommunicationLogChannel::Email->value,
            'source_type' => EmailMessage::class,
            'source_id' => $message->id,
        ]);
    }

    public function test_failed_send_stores_failure_reason(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $message = EmailMessage::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'email_account_id' => EmailAccount::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Default',
                'from_email' => 'noreply@janaprints.test',
                'from_name' => 'Jana Prints',
                'provider' => EmailProvider::Unconfigured,
                'status' => EmailAccountStatus::Active,
                'verification_status' => EmailVerificationStatus::Verified,
                'is_default' => true,
            ])->id,
            'to_emails' => [['email' => 'customer@example.com', 'name' => 'Customer']],
            'subject' => 'Failure test',
            'body' => '<p>Should fail</p>',
            'status' => EmailDeliveryStatus::Queued,
            'queued_at' => now(),
            'created_by' => $user->id,
        ]);

        app(EmailMessageService::class)->deliver($message);

        $message->refresh();

        $this->assertSame(EmailDeliveryStatus::Failed, $message->status);
        $this->assertNotNull($message->failed_at);
        $this->assertNotEmpty($message->failure_reason);
    }

    public function test_sender_resolution_for_department_mailboxes(): void
    {
        Config::set('mailboxes.department.hr', 'hr@janaprints.co.ke');
        Config::set('mailboxes.department.sales', 'sales@janaprints.co.ke');
        Config::set('mailboxes.department.accounts', 'accounts@janaprints.co.ke');
        Config::set('mailboxes.system.notifications', 'notifications@janaprints.co.ke');

        $resolver = app(EmailSenderResolver::class);

        $this->assertSame('hr@janaprints.co.ke', $resolver->resolve('employee_onboarding')->address);
        $this->assertSame('sales@janaprints.co.ke', $resolver->resolve('quotation')->address);
        $this->assertSame('accounts@janaprints.co.ke', $resolver->resolve('receipt')->address);
        $this->assertSame('notifications@janaprints.co.ke', $resolver->resolve('system_alert')->address);
    }

    public function test_corporate_mail_audit_service_records_communication_log(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        Config::set('mailboxes.system.notifications', 'notifications@janaprints.co.ke');

        $message = app(CorporateMailAuditService::class)->recordOutbound([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'to' => [['email' => 'audit@example.com', 'name' => 'Audit Recipient']],
            'subject' => 'Audit trail',
            'body' => '<p>Recorded only</p>',
            'sender_purpose' => 'system_alert',
        ]);

        $this->assertDatabaseHas('email_messages', [
            'id' => $message->id,
            'subject' => 'Audit trail',
        ]);

        $this->assertDatabaseHas('communication_logs', [
            'company_id' => $company->id,
            'channel' => CommunicationLogChannel::Email->value,
            'source_type' => EmailMessage::class,
            'source_id' => $message->id,
        ]);
    }

    public function test_duplicate_jobs_do_not_double_send(): void
    {
        Mail::fake();

        [$company, $branch, $user] = $this->tenantContext();
        $this->createActiveIntegration($company);

        $message = EmailMessage::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'email_account_id' => EmailAccount::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Default',
                'from_email' => 'noreply@janaprints.test',
                'from_name' => 'Jana Prints',
                'provider' => EmailProvider::Unconfigured,
                'status' => EmailAccountStatus::Active,
                'verification_status' => EmailVerificationStatus::Verified,
                'is_default' => true,
            ])->id,
            'to_emails' => [['email' => 'once@example.com', 'name' => 'Once']],
            'subject' => 'Idempotency test',
            'body' => '<p>Once only</p>',
            'status' => EmailDeliveryStatus::Queued,
            'queued_at' => now(),
            'created_by' => $user->id,
        ]);

        $service = app(EmailMessageService::class);
        $service->deliver($message);
        $eventsAfterFirst = $message->fresh()->deliveryEvents()->where('event', 'provider_response')->count();
        $service->deliver($message->fresh());
        $eventsAfterSecond = $message->fresh()->deliveryEvents()->where('event', 'provider_response')->count();

        $this->assertSame(EmailDeliveryStatus::Sent, $message->fresh()->status);
        $this->assertSame(1, $eventsAfterFirst);
        $this->assertSame(1, $eventsAfterSecond);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    protected function createActiveIntegration(Company $company): IntegrationEmailSetting
    {
        return IntegrationEmailSetting::query()->create([
            'company_id' => $company->id,
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
