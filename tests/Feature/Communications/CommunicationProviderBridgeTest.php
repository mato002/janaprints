<?php

namespace Tests\Feature\Communications;

use App\Enums\EmailAccountStatus;
use App\Enums\EmailDeliveryStatus;
use App\Enums\EmailProvider;
use App\Enums\EmailVerificationStatus;
use App\Enums\IntegrationEmailProvider;
use App\Enums\IntegrationSmsProvider;
use App\Enums\SmsCampaignSendMode;
use App\Enums\SmsCampaignStatus;
use App\Enums\SmsRecipientSource;
use App\Enums\IntegrationWhatsappProvider;
use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Enums\WhatsappAccountStatus;
use App\Enums\WhatsappDeliveryStatus;
use App\Enums\WhatsappMessageDirection;
use App\Enums\WhatsappMessageType;
use App\Enums\WhatsappProvider;
use App\Enums\WhatsappVerificationStatus;
use App\Models\Branch;
use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsRecipient;
use App\Models\Communications\SmsProviderLog;
use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappMessage;
use App\Models\Company;
use App\Models\User;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Models\Integrations\IntegrationWhatsappSetting;
use App\Support\Communications\Email\EmailProviderGateway;
use App\Support\Communications\Sms\SmsProviderGateway;
use App\Support\Communications\Whatsapp\WhatsappProviderGateway;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CommunicationProviderBridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_sms_send_uses_active_integration_setting(): void
    {
        Http::fake(['*' => Http::response(['message_id' => 'SMS-123'], 200)]);

        [$company, $branch, $user] = $this->tenantPair();

        IntegrationSmsSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationSmsProvider::Http,
            'api_url' => 'https://sms.bridge.test/send',
            'api_key' => 'secret-key',
            'sender_id' => 'JANAPRINTS',
            'is_active' => true,
            'health_status' => 'healthy',
        ]);

        $message = $this->smsMessage($company, $branch, $user);
        $result = app(SmsProviderGateway::class)->send($message);

        $this->assertTrue($result['success']);
        $this->assertSame(SmsDeliveryStatus::Delivered, $result['delivery_status']);
        $this->assertDatabaseHas('sms_provider_logs', [
            'sms_message_id' => $message->id,
            'provider' => IntegrationSmsProvider::Http->value,
            'http_status' => 200,
        ]);
        Http::assertSent(fn ($request) => $request->url() === 'https://sms.bridge.test/send');
    }

    public function test_email_send_uses_active_integration_setting(): void
    {
        Mail::fake();

        [$company, $branch] = $this->tenantPair();

        IntegrationEmailSetting::query()->create([
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

        [$account, $message] = $this->emailFixtures($company, $branch);
        $result = app(EmailProviderGateway::class)->send($account, $message);

        $this->assertSame(EmailDeliveryStatus::Sent, $result->status);
        $this->assertSame('smtp', $result->payload['provider'] ?? null);
        $this->assertSame(1, $result->payload['failover_attempt'] ?? null);
    }

    public function test_whatsapp_send_uses_active_integration_setting(): void
    {
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.TEST123']]], 200)]);

        [$company, $branch] = $this->tenantPair();

        IntegrationWhatsappSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationWhatsappProvider::MetaCloud,
            'api_key' => 'meta-token',
            'phone_number_id' => '1234567890',
            'is_active' => true,
            'health_status' => 'healthy',
        ]);

        [$account, $message] = $this->whatsappFixtures($company, $branch);
        $result = app(WhatsappProviderGateway::class)->send($account, $message);

        $this->assertNotSame(WhatsappDeliveryStatus::Failed, $result->status);
        $this->assertSame('meta_cloud', $result->payload['provider'] ?? null);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'graph.facebook.com'));
    }

    public function test_sms_provider_failover_on_primary_failure(): void
    {
        Http::fake([
            'https://sms.primary.test/*' => Http::response(['error' => 'down'], 500),
            'https://sms.backup.test/*' => Http::response(['message_id' => 'SMS-BACKUP'], 200),
        ]);

        [$company, $branch, $user] = $this->tenantPair();

        IntegrationSmsSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationSmsProvider::Http,
            'api_url' => 'https://sms.primary.test/send',
            'api_key' => 'primary-key',
            'sender_id' => 'JANA',
            'is_active' => true,
            'health_status' => 'healthy',
        ]);

        IntegrationSmsSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationSmsProvider::Twilio,
            'api_url' => 'https://sms.backup.test/send',
            'api_key' => 'backup-key',
            'username' => 'AC123',
            'sender_id' => 'JANA',
            'is_active' => false,
            'health_status' => 'healthy',
        ]);

        $message = $this->smsMessage($company, $branch, $user);
        $result = app(SmsProviderGateway::class)->send($message);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['response']['failover_attempt'] ?? null);
        $this->assertSame(2, SmsProviderLog::query()->where('sms_message_id', $message->id)->count());
    }

    public function test_provider_attempts_are_logged_with_integration_metadata(): void
    {
        Http::fake(['*' => Http::response(['message_id' => 'LOG-1'], 200)]);

        [$company, $branch, $user] = $this->tenantPair();

        $setting = IntegrationSmsSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationSmsProvider::Onfon,
            'api_url' => 'https://onfon.bridge.test/send',
            'api_key' => 'onfon-key',
            'sender_id' => 'JANA',
            'is_active' => true,
        ]);

        $message = $this->smsMessage($company, $branch, $user);
        app(SmsProviderGateway::class)->send($message);

        $log = SmsProviderLog::query()->where('sms_message_id', $message->id)->firstOrFail();
        $this->assertSame(IntegrationSmsProvider::Onfon->value, $log->provider);
        $this->assertSame($setting->id, $log->request_payload['integration_setting_id'] ?? null);
        $this->assertSame(1, $log->request_payload['failover_attempt'] ?? null);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantPair(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return [$company, $branch, $user];
    }

    protected function smsMessage(Company $company, Branch $branch, User $user): SmsMessage
    {
        $campaign = SmsCampaign::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'campaign_code' => 'SMS-BRIDGE-001',
            'name' => 'Bridge Campaign',
            'message_template' => 'Bridge test SMS',
            'send_mode' => SmsCampaignSendMode::Immediate,
            'status' => SmsCampaignStatus::Sending,
            'recipient_source' => SmsRecipientSource::Manual,
            'created_by' => $user->id,
        ]);

        $recipient = SmsRecipient::query()->create([
            'sms_campaign_id' => $campaign->id,
            'source_type' => 'manual',
            'phone_number' => '+254700000111',
            'display_name' => 'Bridge Recipient',
            'status' => 'queued',
        ]);

        return SmsMessage::query()->create([
            'sms_campaign_id' => $campaign->id,
            'sms_recipient_id' => $recipient->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'phone_number' => '+254700000111',
            'message_body' => 'Bridge test SMS',
            'queue_status' => SmsMessageQueueStatus::Queued,
            'delivery_status' => SmsDeliveryStatus::Queued,
            'segments_count' => 1,
            'character_count' => 14,
            'credit_cost' => 1,
            'attempts' => 0,
        ]);
    }

    /**
     * @return array{0: EmailAccount, 1: EmailMessage}
     */
    protected function emailFixtures(Company $company, Branch $branch): array
    {
        $account = EmailAccount::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Bridge Email',
            'from_email' => 'bridge@janaprints.test',
            'from_name' => 'Bridge',
            'provider' => EmailProvider::Unconfigured,
            'status' => EmailAccountStatus::Active,
            'verification_status' => EmailVerificationStatus::Verified,
            'is_default' => true,
        ]);

        $message = EmailMessage::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'email_account_id' => $account->id,
            'to_emails' => [['email' => 'customer@example.com', 'name' => 'Customer']],
            'subject' => 'Bridge Test',
            'body' => '<p>Bridge email body</p>',
            'status' => EmailDeliveryStatus::Queued,
        ]);

        return [$account, $message];
    }

    /**
     * @return array{0: WhatsappAccount, 1: WhatsappMessage}
     */
    protected function whatsappFixtures(Company $company, Branch $branch): array
    {
        $account = WhatsappAccount::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Bridge WhatsApp',
            'phone_number' => '+254700000222',
            'display_name' => 'Bridge',
            'provider' => WhatsappProvider::MetaCloud,
            'status' => WhatsappAccountStatus::Active,
            'verification_status' => WhatsappVerificationStatus::Verified,
            'is_default' => true,
        ]);

        $conversation = WhatsappConversation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'whatsapp_account_id' => $account->id,
            'conversation_code' => 'WA-BRIDGE-001',
            'phone_number' => '+254711000333',
            'channel' => 'whatsapp',
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);

        $message = WhatsappMessage::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'whatsapp_conversation_id' => $conversation->id,
            'whatsapp_account_id' => $account->id,
            'direction' => WhatsappMessageDirection::Outgoing,
            'message_type' => WhatsappMessageType::Manual,
            'body' => 'Bridge WhatsApp body',
            'status' => WhatsappDeliveryStatus::Queued,
        ]);

        return [$account, $message];
    }
}
