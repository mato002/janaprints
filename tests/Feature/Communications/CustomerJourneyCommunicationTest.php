<?php

namespace Tests\Feature\Communications;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CommunicationTemplateCategory;
use App\Enums\CommunicationTemplateStatus;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\DomainCommunicationEvent;
use App\Enums\FollowUpStatus;
use App\Enums\IntegrationEmailProvider;
use App\Enums\IntegrationSmsProvider;
use App\Enums\IntegrationWhatsappProvider;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\WhatsappAccountStatus;
use App\Enums\WhatsappProvider;
use App\Enums\WhatsappVerificationStatus;
use App\Jobs\Communications\SendSmsMessageJob;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\CommunicationTemplate;
use App\Models\Communications\EmailMessage;
use App\Models\Communications\WhatsappAccount;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadFollowUp;
use App\Models\Crm\LeadStage;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Models\Integrations\IntegrationWhatsappSetting;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Communications\CommunicationEventDispatcher;
use App\Support\Communications\CommunicationScheduledEventScanner;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerJourneyCommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_customer_created_delivers_all_journey_channels(): void
    {
        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::AccountActivated);

        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::CustomerCreated,
            $customer,
            $user,
        );

        $this->assertJourneyChannelsDelivered($company, 'Journey Customer Co');
        $this->assertDomainEventLogged(DomainCommunicationEvent::CustomerCreated, 'customer', $customer->id);
    }

    public function test_quotation_sent_delivers_all_journey_channels(): void
    {
        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::QuotationReady);

        $quotation = Quotation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QT-JOURNEY-001',
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
            'prepared_by' => $user->id,
        ]);

        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::QuotationSent,
            $quotation,
            $user,
        );

        $this->assertJourneyChannelsDelivered($company, 'Journey Customer Co');
        $this->assertDomainEventLogged(DomainCommunicationEvent::QuotationSent, 'quotation', $quotation->id);
    }

    public function test_artwork_approved_delivers_all_journey_channels(): void
    {
        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::ArtworkApproved);

        $artwork = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Approved,
        ]);

        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::ArtworkApproved,
            $artwork,
            $user,
        );

        $this->assertJourneyChannelsDelivered($company, 'Journey Customer Co');
        $this->assertDomainEventLogged(DomainCommunicationEvent::ArtworkApproved, 'artwork_request', $artwork->id);
    }

    public function test_invoice_generated_delivers_all_journey_channels(): void
    {
        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::InvoiceGenerated);

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-JOURNEY-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 1500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1500,
            'balance_due' => 1500,
            'amount_paid' => 0,
            'created_by' => $user->id,
        ]);

        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::InvoiceGenerated,
            $invoice,
            $user,
        );

        $this->assertJourneyChannelsDelivered($company, 'Journey Customer Co');
        $this->assertDomainEventLogged(DomainCommunicationEvent::InvoiceGenerated, 'customer_invoice', $invoice->id);
    }

    public function test_payment_reminder_delivers_all_journey_channels(): void
    {
        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::InvoiceOverdue);

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-JOURNEY-OVERDUE',
            'invoice_date' => now()->subDays(30)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 900,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 900,
            'balance_due' => 900,
            'amount_paid' => 0,
            'created_by' => $user->id,
        ]);

        $this->artisan('communications:payment-reminders')->assertSuccessful();

        $this->assertJourneyChannelsDelivered($company, 'Journey Customer Co');
        $this->assertDomainEventLogged(DomainCommunicationEvent::InvoiceOverdue, 'customer_invoice', $invoice->id);
    }

    public function test_payment_received_delivers_all_journey_channels(): void
    {
        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::PaymentReceived);

        $payment = CustomerPayment::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'payment_number' => 'RCP-JOURNEY-001',
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Bank,
            'amount' => 500,
            'allocated_amount' => 0,
            'unallocated_amount' => 500,
            'status' => CustomerPaymentStatus::Posted,
            'created_by' => $user->id,
            'posted_by' => $user->id,
            'posted_at' => now(),
        ]);

        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::PaymentReceived,
            $payment,
            $user,
        );

        $this->assertJourneyChannelsDelivered($company, 'Journey Customer Co');
        $this->assertDomainEventLogged(DomainCommunicationEvent::PaymentReceived, 'customer_payment', $payment->id);
    }

    public function test_delivery_completed_delivers_all_journey_channels(): void
    {
        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::Delivered);

        $deliveryNote = DeliveryNote::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'delivery_note_number' => 'DN-JOURNEY-001',
            'customer_id' => $customer->id,
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryNoteStatus::Delivered,
            'recipient_name' => $customer->company_name,
            'recipient_phone' => $customer->phone,
            'delivered_by' => $user->id,
            'delivered_at' => now(),
        ]);

        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::DeliveryCompleted,
            $deliveryNote,
            $user,
        );

        $this->assertJourneyChannelsDelivered($company, 'Journey Customer Co');
        $this->assertDomainEventLogged(DomainCommunicationEvent::DeliveryCompleted, 'delivery_note', $deliveryNote->id);
    }

    public function test_follow_up_due_alerts_staff_and_reminds_customer(): void
    {
        Mail::fake();
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.FU123']]], 200)]);

        [$company, $branch, $user, $customer] = $this->journeyContext(CommunicationTemplateCategory::QuotationReady);
        (new CrmFoundationSeeder)->run();

        $lead = Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_name' => 'Follow Up Lead',
            'email' => $customer->email,
            'phone' => $customer->phone,
            'status' => LeadStatus::Open,
            'stage_id' => LeadStage::query()->where('company_id', $company->id)->value('id'),
        ]);

        $followUp = LeadFollowUp::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_id' => $lead->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'scheduled_at' => now()->subMinutes(5),
            'status' => FollowUpStatus::Pending,
            'notes' => 'Call customer',
        ]);

        $this->artisan('communications:follow-up-due')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'company_id' => $company->id,
            'recipient_user_id' => $user->id,
            'title' => 'Follow-up due',
        ]);

        $this->assertDatabaseHas('email_messages', [
            'company_id' => $company->id,
        ]);

        $this->assertDomainEventLogged(DomainCommunicationEvent::FollowUpDue, 'lead_follow_up', $followUp->id);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: Customer}
     */
    protected function journeyContext(CommunicationTemplateCategory $category): array
    {
        Mail::fake();
        Queue::fake();
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.JOURNEY']]], 200)]);

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
            'customer_code' => 'CUST-JOURNEY-'.strtoupper(substr($category->value, 0, 4)),
            'customer_type' => CustomerType::Corporate,
            'company_name' => 'Journey Customer Co',
            'email' => 'journey@customer.test',
            'phone' => '+254711222333',
            'status' => CustomerStatus::Active,
        ]);

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

        IntegrationSmsSetting::query()->create([
            'company_id' => $company->id,
            'provider' => IntegrationSmsProvider::Http,
            'api_url' => 'https://sms.journey.test/send',
            'api_key' => 'sms-key',
            'sender_id' => 'JANA',
            'is_active' => true,
        ]);

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
            'name' => 'Journey WhatsApp',
            'phone_number' => '+254700000100',
            'display_name' => 'Jana Prints',
            'provider' => WhatsappProvider::MetaCloud,
            'status' => WhatsappAccountStatus::Active,
            'verification_status' => WhatsappVerificationStatus::Verified,
            'is_default' => true,
            'created_by' => $user->id,
        ]);

        $this->createJourneyTemplates($company, $user, $category);

        return [$company, $branch, $user, $customer];
    }

    protected function createJourneyTemplates(
        Company $company,
        User $user,
        CommunicationTemplateCategory $category,
    ): void {
        foreach (['email', 'sms', 'whatsapp'] as $channel) {
            CommunicationTemplate::query()->create([
                'company_id' => $company->id,
                'code' => 'journey_'.$category->value.'_'.$channel,
                'name' => 'Journey '.$category->label().' '.$channel,
                'category' => $category->value,
                'channel' => $channel,
                'template_type' => 'transactional',
                'subject' => $category->label().' {{document_number}}',
                'body' => 'Hello {{customer_name}}, '.$category->label(),
                'status' => CommunicationTemplateStatus::Active,
                'created_by' => $user->id,
            ]);
        }
    }

    protected function assertJourneyChannelsDelivered(Company $company, string $customerName): void
    {
        $email = EmailMessage::query()->where('company_id', $company->id)->latest('id')->first();
        $this->assertNotNull($email, 'Email should be sent');
        $this->assertStringContainsString($customerName, $email->body, 'Template should render customer name in email');

        $this->assertDatabaseHas('sms_messages', [
            'company_id' => $company->id,
            'phone_number' => '+254711222333',
        ]);
        Queue::assertPushed(SendSmsMessageJob::class);

        $this->assertDatabaseHas('whatsapp_messages', [
            'company_id' => $company->id,
        ]);

        $this->assertGreaterThan(0, CommunicationLog::query()
            ->where('company_id', $company->id)
            ->where('channel', 'email')
            ->count(), 'Email communication log should be created');

        $this->assertDatabaseHas('communication_logs', [
            'company_id' => $company->id,
            'channel' => 'system',
        ]);
    }

    protected function assertDomainEventLogged(
        DomainCommunicationEvent $event,
        string $sourceType,
        int $sourceId,
    ): void {
        $this->assertDatabaseHas('communication_logs', [
            'template_code' => $event->value,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);
    }
}
