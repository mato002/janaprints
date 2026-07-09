<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\PublicHash\PublicHashValidationException;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\Sales\CustomerPaymentReceiptService;
use App\Support\Sales\CustomerPaymentService;
use App\Support\TenantContext;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicHashCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(CrmFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(JanaPrintsTaxSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()
            ->where('company_id', $this->company->id)
            ->where('code', 'HQ')
            ->firstOrFail();

        $this->admin = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->admin->assignRole('Company Admin');

        session([
            'active_company_id' => $this->company->id,
            'active_branch_id' => $this->branch->id,
        ]);

        Config::set('public_hashes.numeric_fallback_enabled', false);
    }

    public function test_certification_command_passes_in_strict_mode(): void
    {
        $exitCode = Artisan::call('public-hash:certify', ['--strict' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('PUBLIC HASH SECURITY CERTIFIED', Artisan::output());
    }

    public function test_certification_command_fails_when_configured_model_missing_public_id(): void
    {
        Customer::withoutEvents(function () {
            Customer::query()->forceCreate([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'customer_code' => 'CUST-NOCERT',
                'customer_type' => 'corporate',
                'company_name' => 'Uncertified Co',
                'status' => 'active',
                'public_id' => null,
            ]);
        });

        $exitCode = Artisan::call('public-hash:certify', ['--strict' => true]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('REMEDIATION REQUIRED', Artisan::output());
    }

    public function test_numeric_fallback_disabled_rejects_numeric_admin_url(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/crm/customers/'.$customer->id)
            ->assertNotFound();
    }

    public function test_hash_url_still_works_after_fallback_disabled(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/crm/customers/'.$customer->public_id)
            ->assertOk();
    }

    public function test_client_numeric_url_rejected_after_fallback_disabled(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $clientUser = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $this->admin->id,
        ]);

        $this->withSession(['auth_context' => 'client'])
            ->actingAs($clientUser)
            ->get('/client/quotations/'.$quotation->id)
            ->assertNotFound();
    }

    public function test_signed_hash_receipt_url_works_with_fallback_disabled(): void
    {
        $payment = $this->postedPayment();

        app()->instance(TenantContext::class, new TenantContext(null, null, false));

        $url = app(CustomerPaymentReceiptService::class)->signedPublicUrl($payment);

        $this->get($url)
            ->assertOk()
            ->assertSee($payment->receipt_number);
    }

    public function test_legacy_numeric_signed_receipt_url_still_works_with_route_specific_compat(): void
    {
        $payment = $this->postedPayment();

        app()->instance(TenantContext::class, new TenantContext(null, null, false));

        $url = URL::temporarySignedRoute(
            'public.payment-receipt.show',
            now()->addHour(),
            ['payment' => $payment->id],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee($payment->receipt_number);
    }

    public function test_unknown_numeric_admin_url_returns_not_found(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/crm/customers/999999')
            ->assertNotFound();
    }

    public function test_audit_strict_routes_views_js_passes_for_certified_scope(): void
    {
        $exitCode = Artisan::call('public-hash:audit', [
            '--strict' => true,
            '--routes' => true,
            '--views' => true,
            '--js' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Issues found: 0', Artisan::output());
    }

    public function test_stored_url_audit_runs(): void
    {
        $exitCode = Artisan::call('public-hash:audit-stored-urls');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('report-only', strtolower(Artisan::output()));
    }

    public function test_newly_created_configured_model_gets_non_null_public_id(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->assertNotNull($customer->public_id);
        $this->assertSame(16, strlen((string) $customer->public_id));
    }

    public function test_public_id_cannot_be_changed(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->expectException(PublicHashValidationException::class);

        $customer->public_id = str_repeat('b', 16);
        $customer->save();
    }

    public function test_public_id_column_not_nullable_check_skipped_on_sqlite(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('SQLite test harness does not enforce NOT NULL schema changes.');
        }

        $columns = Schema::getConnection()->getSchemaBuilder()->getColumns('customers');
        $publicId = null;

        foreach ($columns as $definition) {
            if (($definition['name'] ?? null) === 'public_id') {
                $publicId = $definition;
                break;
            }
        }

        $this->assertNotNull($publicId);
        $this->assertFalse((bool) ($publicId['nullable'] ?? true));
    }

    public function test_composer_public_hash_check_script_is_defined(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('public-hash:check', $composer['scripts']);
        $this->assertContains('@php artisan public-hash:certify --strict', $composer['scripts']['public-hash:check']);
    }

    protected function postedPayment(): CustomerPayment
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'email' => 'pay@example.com',
        ]);

        $order = \App\Models\Sales\SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'status' => \App\Enums\SalesOrderStatus::Confirmed,
            'created_by' => $this->admin->id,
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
        ]);
        $order->items()->create([
            'item_name' => 'Cards',
            'quantity' => 100,
            'unit_price' => 10,
            'line_total' => 1000,
            'sort_order' => 1,
        ]);

        $invoice = app(CustomerInvoiceService::class)->createFromSalesOrder($order, $this->admin->id);
        app(CustomerInvoiceService::class)->approve($invoice, $this->admin->id);
        app(CustomerInvoiceService::class)->post($invoice->fresh(), $this->admin->id);

        $payment = app(CustomerPaymentService::class)->create($customer, $this->admin->id, [
            'payment_date' => now()->toDateString(),
            'payment_method' => \App\Enums\CustomerPaymentMethod::Bank,
            'amount' => $invoice->fresh()->total_amount,
            'allocations' => [
                ['customer_invoice_id' => $invoice->id, 'amount' => $invoice->total_amount],
            ],
        ]);

        return app(CustomerPaymentService::class)->post($payment, $this->admin->id);
    }
}
