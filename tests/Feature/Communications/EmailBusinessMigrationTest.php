<?php

namespace Tests\Feature\Communications;

use App\Enums\EmailDeliveryStatus;
use App\Enums\IntegrationEmailProvider;
use App\Jobs\Communications\SendEmailMessageJob;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingEmailJob;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\EmailMessage;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\PublicContactMessage;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Services\Storefront\PublicContactMessageService;
use App\Services\Storefront\PublicQuoteRequestService;
use App\Support\Communications\Email\CorporateMailDispatcher;
use App\Support\Sales\CustomerPaymentReceiptService;
use App\Support\Sales\SalesDocumentEmailService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailBusinessMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_corporate_mail_dispatcher_queues_email_message(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenant();

        $message = app(CorporateMailDispatcher::class)->dispatch([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'user_id' => $user->id,
            'to' => [['email' => 'audit@example.com', 'name' => 'Audit']],
            'subject' => 'Migration test',
            'body' => '<p>Queued through dispatcher</p>',
            'sender_purpose' => 'system_alert',
            'metadata' => ['module' => 'test'],
        ]);

        $this->assertNotNull($message);
        $this->assertSame(EmailDeliveryStatus::Queued, $message->status);
        Queue::assertPushed(SendEmailMessageJob::class);
        $this->assertDatabaseHas('email_messages', [
            'id' => $message->id,
            'subject' => 'Migration test',
        ]);
    }

    public function test_password_reset_uses_corporate_mail_channel(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->sendPasswordResetNotification('test-token');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $payload = $notification->toCorporateMail($user);

            return $payload['sender_purpose'] === 'password_reset'
                && $payload['to'][0]['email'] === $user->email
                && str_contains($payload['body'], '/reset-password/');
        });
    }

    public function test_storefront_quote_request_queues_emails(): void
    {
        Queue::fake();

        app(PublicQuoteRequestService::class)->store([
            'name' => 'Guest User',
            'phone' => '+254700000001',
            'email' => 'guest@example.com',
            'service_needed' => 'Business Cards',
            'message' => 'Need 500 cards',
        ]);

        $this->assertGreaterThan(0, EmailMessage::query()->count());
        Queue::assertPushed(SendEmailMessageJob::class);
    }

    public function test_storefront_contact_message_queues_emails(): void
    {
        Queue::fake();

        app(PublicContactMessageService::class)->store([
            'name' => 'Guest Contact',
            'email' => 'contact@example.com',
            'subject' => 'Hello',
            'message' => 'Need help',
        ]);

        $this->assertGreaterThan(0, EmailMessage::query()->count());
        Queue::assertPushed(SendEmailMessageJob::class);
    }

    public function test_sender_purposes_resolve_expected_mailboxes(): void
    {
        Config::set('mailboxes.department.hr', 'hr@janaprints.co.ke');
        Config::set('mailboxes.department.sales', 'sales@janaprints.co.ke');
        Config::set('mailboxes.department.accounts', 'accounts@janaprints.co.ke');
        Config::set('mailboxes.system.noreply', 'noreply@janaprints.co.ke');

        [$company, $branch, $user] = $this->tenant();
        $this->createIntegration($company);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $dispatcher = app(CorporateMailDispatcher::class);

        $hr = $dispatcher->dispatch([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'to' => [['email' => 'hr-test@example.com']],
            'subject' => 'HR',
            'body' => '<p>HR</p>',
            'sender_purpose' => 'employee_onboarding',
        ]);

        $sales = $dispatcher->dispatch([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'to' => [['email' => 'sales-test@example.com']],
            'subject' => 'Sales',
            'body' => '<p>Sales</p>',
            'sender_purpose' => 'quotation',
        ]);

        $this->assertSame('hr@janaprints.co.ke', $hr?->account?->from_email);
        $this->assertSame('sales@janaprints.co.ke', $sales?->account?->from_email);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenant(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $user];
    }

    protected function createIntegration(Company $company): IntegrationEmailSetting
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
