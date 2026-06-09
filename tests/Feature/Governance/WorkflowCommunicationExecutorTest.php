<?php

namespace Tests\Feature\Governance;

use App\Enums\CommunicationTemplateStatus;
use App\Enums\IntegrationEmailProvider;
use App\Enums\IntegrationSmsProvider;
use App\Enums\IntegrationWhatsappProvider;
use App\Enums\QuotationStatus;
use App\Enums\WhatsappAccountStatus;
use App\Enums\WhatsappProvider;
use App\Enums\WhatsappVerificationStatus;
use App\Enums\WorkflowRuleActionType;
use App\Enums\WorkflowRuleExecutionStatus;
use App\Enums\WorkflowRuleStatus;
use App\Enums\WorkflowRuleTrigger;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\WhatsappAccount;
use App\Models\Communications\EmailMessage;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\WhatsappMessage;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Governance\WorkflowRule;
use App\Models\Governance\WorkflowRuleAction;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Models\Integrations\IntegrationWhatsappSetting;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Jobs\Communications\SendSmsMessageJob;
use App\Support\Governance\WorkflowCommunicationService;
use App\Support\Governance\WorkflowRulesService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WorkflowCommunicationExecutorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_email_workflow_sends_and_logs(): void
    {
        Mail::fake();

        [$company, $branch, $user, $quotation] = $this->quotationContext();

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

        $this->createWorkflowRule($company, WorkflowRuleActionType::SendEmail, [
            'recipient_email' => 'customer@workflow.test',
            'subject' => 'Quote {{document_number}} ready',
            'body' => '<p>Hello {{customer_name}}</p>',
        ]);

        $executions = app(WorkflowRulesService::class)->dispatch(
            WorkflowRuleTrigger::Approved,
            $quotation,
            $user,
        );

        $this->assertTrue($executions->contains(
            fn ($execution) => $execution->status === WorkflowRuleExecutionStatus::Completed
                && ($execution->result_json['success'] ?? false) === true,
        ));

        $this->assertDatabaseHas('email_messages', [
            'company_id' => $company->id,
            'subject' => 'Quote '.$quotation->quotation_number.' ready',
        ]);

        $this->assertGreaterThan(0, CommunicationLog::query()->where('channel', 'email')->count());
    }

    public function test_sms_workflow_dispatches_queued_job_with_retries(): void
    {
        Queue::fake();

        [$company, $branch, $user, $quotation] = $this->quotationContext();

        IntegrationSmsSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationSmsProvider::Http,
            'api_url' => 'https://sms.workflow.test/send',
            'api_key' => 'sms-key',
            'sender_id' => 'JANA',
            'is_active' => true,
        ]);

        $this->createWorkflowRule($company, WorkflowRuleActionType::SendSms, [
            'recipient_phone' => '+254711222333',
            'message' => 'Quote {{document_number}} approved.',
            'queue' => true,
        ]);

        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Approved, $quotation, $user);

        Queue::assertPushed(SendSmsMessageJob::class, function (SendSmsMessageJob $job) {
            return $job->tries === 3;
        });

        $this->assertDatabaseHas('sms_messages', [
            'company_id' => $company->id,
            'phone_number' => '+254711222333',
        ]);
    }

    public function test_whatsapp_workflow_sends_and_logs(): void
    {
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.WF123']]], 200)]);

        [$company, $branch, $user, $quotation] = $this->quotationContext();

        IntegrationWhatsappSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationWhatsappProvider::MetaCloud,
            'api_key' => 'meta-token',
            'phone_number_id' => '1234567890',
            'is_active' => true,
        ]);

        WhatsappAccount::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => 'Workflow WhatsApp',
            'phone_number' => '+254700000100',
            'display_name' => 'Jana Prints',
            'provider' => WhatsappProvider::MetaCloud,
            'status' => WhatsappAccountStatus::Active,
            'verification_status' => WhatsappVerificationStatus::Verified,
            'is_default' => true,
            'created_by' => $user->id,
        ]);

        $this->createWorkflowRule($company, WorkflowRuleActionType::SendWhatsapp, [
            'recipient_phone' => '+254711222333',
            'message' => 'Your quote {{document_number}} is ready.',
        ]);

        $executions = app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Approved, $quotation, $user);

        $this->assertTrue($executions->contains(
            fn ($execution) => $execution->status === WorkflowRuleExecutionStatus::Completed
                && ($execution->result_json['success'] ?? false) === true,
        ));

        $this->assertDatabaseHas('whatsapp_messages', [
            'company_id' => $company->id,
        ]);

        $this->assertGreaterThan(0, CommunicationLog::query()->where('channel', 'whatsapp')->count());
    }

    public function test_failure_handling_skips_when_recipient_missing(): void
    {
        [$company, $branch, $user, $quotation] = $this->quotationContext(customerPhone: null, customerEmail: null);

        $result = app(WorkflowCommunicationService::class)->sendSms($quotation, [
            'message' => 'No phone on file',
        ], $user);

        $this->assertTrue($result['skipped'] ?? false);
        $this->assertSame('no_recipient_phone', $result['reason'] ?? null);
    }

    public function test_sms_workflow_sync_failure_is_logged(): void
    {
        Http::fake(['*' => Http::response(['error' => 'provider down'], 500)]);

        [$company, $branch, $user, $quotation] = $this->quotationContext();

        IntegrationSmsSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationSmsProvider::Http,
            'api_url' => 'https://sms.fail.test/send',
            'api_key' => 'sms-key',
            'sender_id' => 'JANA',
            'is_active' => true,
        ]);

        $result = app(WorkflowCommunicationService::class)->sendSms($quotation, [
            'recipient_phone' => '+254711222333',
            'message' => 'Failure path',
            'queue' => false,
        ], $user);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['communication_log_recorded'] ?? false);
        $this->assertDatabaseHas('sms_provider_logs', [
            'provider' => IntegrationSmsProvider::Http->value,
        ]);
    }

    public function test_template_rendering_used_in_workflow_email(): void
    {
        Mail::fake();

        [$company, $branch, $user, $quotation] = $this->quotationContext();

        IntegrationEmailSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationEmailProvider::Smtp,
            'from_email' => 'noreply@janaprints.test',
            'smtp_host' => 'smtp.test.local',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'pass',
            'is_active' => true,
        ]);

        CommunicationTemplate::query()->create([
            'company_id' => $company->id,
            'code' => 'wf_quote_ready',
            'name' => 'Workflow Quote Ready',
            'category' => 'quotation_ready',
            'channel' => 'email',
            'template_type' => 'transactional',
            'subject' => 'Quote {{document_number}}',
            'body' => 'Dear {{customer_name}}, your quote is ready.',
            'status' => CommunicationTemplateStatus::Active,
            'created_by' => $user->id,
        ]);

        $result = app(WorkflowCommunicationService::class)->sendEmail($quotation, [
            'recipient_email' => 'customer@workflow.test',
            'template_code' => 'wf_quote_ready',
        ], $user);

        $this->assertTrue($result['success']);
        $this->assertSame('wf_quote_ready', $result['template_code']);

        $email = EmailMessage::query()->latest('id')->first();
        $this->assertStringContainsString('Acme Workflow Customer', $email->body);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function createWorkflowRule(Company $company, WorkflowRuleActionType $actionType, array $config): WorkflowRule
    {
        $rule = WorkflowRule::query()->create([
            'company_id' => $company->id,
            'name' => 'Workflow Comm Test '.$actionType->value,
            'module' => 'commercial',
            'entity_type' => 'quotation',
            'trigger' => WorkflowRuleTrigger::Approved,
            'status' => WorkflowRuleStatus::Active,
        ]);

        WorkflowRuleAction::query()->create([
            'workflow_rule_id' => $rule->id,
            'sort_order' => 1,
            'action_type' => $actionType,
            'config_json' => $config,
        ]);

        return $rule;
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: Quotation}
     */
    protected function quotationContext(?string $customerPhone = '+254700000999', ?string $customerEmail = 'customer@workflow.test'): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $customer = Customer::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-WF-001',
            'customer_type' => 'corporate',
            'company_name' => 'Acme Workflow Customer',
            'email' => $customerEmail,
            'phone' => $customerPhone,
            'status' => 'active',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $quotation = Quotation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QT-WF-001',
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
            'prepared_by' => $user->id,
        ]);

        return [$company, $branch, $user, $quotation];
    }
}
