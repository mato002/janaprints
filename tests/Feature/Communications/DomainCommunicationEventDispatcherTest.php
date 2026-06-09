<?php

namespace Tests\Feature\Communications;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\DomainCommunicationEvent;
use App\Enums\FollowUpStatus;
use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadFollowUp;
use App\Models\Crm\LeadStage;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Communications\CommunicationEventDispatcher;
use App\Support\Communications\CommunicationScheduledEventScanner;
use App\Support\Crm\LeadConversionService;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DomainCommunicationEventDispatcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_customer_created_dispatches_domain_event_log(): void
    {
        [$company, $branch, $user] = $this->tenantContext(['crm.customers.create']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.crm.customers.store'), [
                'customer_type' => CustomerType::Corporate->value,
                'company_name' => 'Dispatch Test Co',
                'email' => 'dispatch@example.com',
                'phone' => '254700000001',
                'status' => CustomerStatus::Active->value,
            ])
            ->assertRedirect();

        $customer = Customer::query()->where('company_name', 'Dispatch Test Co')->firstOrFail();

        $this->assertDomainEventLogged(DomainCommunicationEvent::CustomerCreated, 'customer', $customer->id);
    }

    public function test_lead_conversion_dispatches_lead_converted_and_customer_created(): void
    {
        [$company, $branch] = $this->tenantContext();

        $lead = Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_name' => 'Convert Me',
            'company_name' => 'Convert Co',
            'email' => 'convert@example.com',
            'phone' => '254700000002',
            'status' => LeadStatus::Open,
            'stage_id' => LeadStage::query()->where('company_id', $company->id)->value('id'),
        ]);

        app(LeadConversionService::class)->convert($lead);

        $this->assertDomainEventLogged(DomainCommunicationEvent::LeadConverted, 'lead', $lead->id);
        $this->assertDomainEventLogged(DomainCommunicationEvent::CustomerCreated, 'customer', $lead->fresh()->customer_id);
    }

    public function test_quotation_sent_dispatches_domain_event_log(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $customer = Customer::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-DCE-001',
            'customer_type' => CustomerType::Corporate,
            'company_name' => 'Quote Customer',
            'status' => CustomerStatus::Active,
        ]);

        $quotation = Quotation::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QT-DCE-001',
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'subtotal' => 1000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 1000,
            'prepared_by' => $user->id,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        app(CommunicationEventDispatcher::class)->dispatch(
            DomainCommunicationEvent::QuotationSent,
            $quotation,
            $user,
        );

        $this->assertDomainEventLogged(DomainCommunicationEvent::QuotationSent, 'quotation', $quotation->id);
    }

    public function test_scheduled_scanner_dispatches_overdue_invoice_and_due_follow_up(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $customer = Customer::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-DCE-002',
            'customer_type' => CustomerType::Corporate,
            'company_name' => 'Overdue Customer',
            'status' => CustomerStatus::Active,
        ]);

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-DCE-001',
            'invoice_date' => now()->subDays(30)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 500,
            'balance_due' => 500,
            'amount_paid' => 0,
            'created_by' => $user->id,
        ]);

        $lead = Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_name' => 'Follow Up Lead',
            'status' => LeadStatus::Open,
            'stage_id' => LeadStage::query()->where('company_id', $company->id)->value('id'),
        ]);

        $followUp = LeadFollowUp::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_id' => $lead->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'scheduled_at' => now()->subHour(),
            'status' => FollowUpStatus::Pending,
            'notes' => 'Call back',
        ]);

        $results = app(CommunicationScheduledEventScanner::class)->scan();

        $this->assertSame(1, $results['invoice_overdue']);
        $this->assertSame(1, $results['follow_up_due']);
        $this->assertDomainEventLogged(DomainCommunicationEvent::InvoiceOverdue, 'customer_invoice', $invoice->id);
        $this->assertDomainEventLogged(DomainCommunicationEvent::FollowUpDue, 'lead_follow_up', $followUp->id);
    }

    public function test_scheduled_scanner_deduplicates_same_day_dispatches(): void
    {
        [$company, $branch, $user] = $this->tenantContext();

        $customer = Customer::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-DCE-003',
            'customer_type' => CustomerType::Corporate,
            'company_name' => 'Dedup Customer',
            'status' => CustomerStatus::Active,
        ]);

        CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-DCE-002',
            'invoice_date' => now()->subDays(30)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 200,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 200,
            'balance_due' => 200,
            'amount_paid' => 0,
            'created_by' => $user->id,
        ]);

        $scanner = app(CommunicationScheduledEventScanner::class);
        $first = $scanner->scan();
        $second = $scanner->scan();

        $this->assertSame(1, $first['invoice_overdue']);
        $this->assertSame(0, $second['invoice_overdue']);
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

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(array $permissions = []): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        (new CrmFoundationSeeder)->run();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return [$company, $branch, $user];
    }
}
