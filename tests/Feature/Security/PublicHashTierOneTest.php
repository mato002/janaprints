<?php

namespace Tests\Feature\Security;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerPaymentMethod;
use App\Enums\CustomerPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadStage;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Communications\NotificationService;
use App\Support\PublicHash\PublicHashGenerator;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PublicHashTierOneTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected PublicHashGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(CrmFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()
            ->where('company_id', $this->company->id)
            ->where('code', 'HQ')
            ->firstOrFail();

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->user->assignRole('Company Admin');

        session([
            'active_company_id' => $this->company->id,
            'active_branch_id' => $this->branch->id,
        ]);

        $this->generator = app(PublicHashGenerator::class);
    }

    public function test_customer_show_route_accepts_hash(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/crm/customers/'.$customer->public_id)
            ->assertOk();
    }

    public function test_quotation_show_route_accepts_hash(): void
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'prepared_by' => $this->user->id,
            'status' => QuotationStatus::Draft,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/quotations/list/'.$quotation->public_id)
            ->assertOk();
    }

    public function test_sales_order_show_route_accepts_hash(): void
    {
        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'status' => SalesOrderStatus::Draft,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/sales-orders/list/'.$order->public_id)
            ->assertOk();
    }

    public function test_job_card_show_route_accepts_hash(): void
    {
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'created_by' => $this->user->id,
            'status' => ProductionJobCardStatus::Draft,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/production/job-cards/'.$jobCard->public_id)
            ->assertOk();
    }

    public function test_invoice_show_route_accepts_hash(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-PH-001',
            'invoice_type' => CustomerInvoiceType::Standard,
            'invoice_date' => now()->toDateString(),
            'status' => CustomerInvoiceStatus::Draft,
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'currency' => 'KES',
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/invoices/'.$invoice->public_id)
            ->assertOk();
    }

    public function test_payment_show_route_accepts_hash(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $payment = CustomerPayment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-PH-001',
            'payment_date' => now()->toDateString(),
            'payment_method' => CustomerPaymentMethod::Cash,
            'amount' => 500,
            'allocated_amount' => 0,
            'unallocated_amount' => 500,
            'currency' => 'KES',
            'status' => CustomerPaymentStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/payments/'.$payment->public_id)
            ->assertOk();
    }

    public function test_lead_show_route_accepts_hash(): void
    {
        $stage = LeadStage::query()->where('company_id', $this->company->id)->firstOrFail();

        $lead = Lead::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'stage_id' => $stage->id,
            'lead_name' => 'Hash Lead',
            'status' => LeadStatus::Open,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/crm/leads/'.$lead->public_id)
            ->assertOk();
    }

    public function test_numeric_fallback_still_works_for_tier_one_routes(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        Config::set('public_hashes.numeric_fallback_enabled', true);

        $this->actingAs($this->user)
            ->get('/admin/crm/customers/'.$customer->id)
            ->assertOk();
    }

    public function test_unknown_hash_returns_not_found(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/crm/customers/aaaaaaaaaaaaaaaa')
            ->assertNotFound();
    }

    public function test_cross_tenant_hash_is_blocked(): void
    {
        $otherCompany = Company::factory()->create(['code' => 'OTHER']);
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id, 'code' => 'OB']);

        $foreignCustomer = Customer::factory()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
        ]);

        $this->actingAs($this->user)
            ->get('/admin/crm/customers/'.$foreignCustomer->public_id)
            ->assertNotFound();
    }

    public function test_new_tier_one_records_auto_generate_public_id(): void
    {
        $models = [
            Customer::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
            ]),
            Quotation::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'prepared_by' => $this->user->id,
            ]),
            SalesOrder::factory()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'created_by' => $this->user->id,
            ]),
        ];

        foreach ($models as $model) {
            $this->assertNotNull($model->public_id);
            $this->assertTrue($this->generator->isValid($model->public_id));
        }
    }

    public function test_backfill_fills_missing_tier_one_public_ids(): void
    {
        $customer = Customer::withoutEvents(function () {
            return Customer::query()->forceCreate([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'customer_code' => 'CUST-NOHASH',
                'customer_type' => 'corporate',
                'company_name' => 'No Hash Co',
                'status' => 'active',
                'public_id' => null,
            ]);
        });

        Artisan::call('public-hash:backfill', [
            '--model' => Customer::class,
        ]);

        $customer->refresh();

        $this->assertNotNull($customer->public_id);
        $this->assertTrue($this->generator->isValid($customer->public_id));
        $this->assertStringContainsString('Backfilled 1 row(s)', Artisan::output());
    }

    public function test_notification_action_urls_emit_hash_for_tier_one_models(): void
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'prepared_by' => $this->user->id,
        ]);

        $notification = app(NotificationService::class)->create([
            'company_id' => $this->company->id,
            'recipient_user_id' => $this->user->id,
            'type' => NotificationType::QuotationApproved,
            'title' => 'Quotation approved',
            'body' => 'Ready for review.',
            'priority' => NotificationPriority::Normal,
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertNotNull($notification);

        $actionUrl = app(NotificationService::class)->resolveActionUrl($notification);

        $this->assertNotNull($actionUrl);
        $this->assertStringContainsString($quotation->public_id, $actionUrl);
        $this->assertStringNotContainsString('/list/'.$quotation->id, $actionUrl);
    }

    public function test_generated_show_links_do_not_include_raw_numeric_id(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertShowRouteUsesPublicHash(route('admin.crm.customers.show', $customer), $customer);
        $this->assertShowRouteUsesPublicHash(route('admin.sales-orders.show', $order), $order);
    }

    protected function assertShowRouteUsesPublicHash(string $url, Model $model): void
    {
        $this->assertStringContainsString((string) $model->public_id, $url);
        $this->assertDoesNotMatchRegularExpression(
            '#/'.preg_quote((string) $model->id, '#').'(?:\?|$)#',
            $url,
        );
    }
}
