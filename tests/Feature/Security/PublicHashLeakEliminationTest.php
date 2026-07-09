<?php

namespace Tests\Feature\Security;

use App\Enums\CustomerPaymentStatus;
use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Communications\NotificationService;
use App\Support\Production\ProductionQueueService;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\ProductionFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PublicHashLeakEliminationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(CrmFoundationSeeder::class);
        $this->seed(ProductionFoundationSeeder::class);
        $this->seed(InventoryFoundationSeeder::class);

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
    }

    public function test_customer_360_page_does_not_render_numeric_customer_show_url(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.crm.customers.show', $customer));

        $response->assertOk();
        $this->assertDoesNotLeakNumericShowUrl($response->getContent(), 'admin/crm/customers', $customer->id);
        $response->assertSee($customer->public_id, false);
    }

    public function test_sales_order_show_does_not_render_numeric_order_url(): void
    {
        $order = $this->makeSalesOrder();

        $response = $this->actingAs($this->user)
            ->get(route('admin.sales-orders.show', $order));

        $response->assertOk();
        $this->assertDoesNotLeakNumericShowUrl($response->getContent(), 'admin/sales-orders/list', $order->id);
    }

    public function test_quotation_show_does_not_render_numeric_quotation_url(): void
    {
        $quotation = Quotation::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'prepared_by' => $this->user->id,
            'status' => QuotationStatus::Draft,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.quotations.show', $quotation));

        $response->assertOk();
        $this->assertDoesNotLeakNumericShowUrl($response->getContent(), 'admin/quotations/list', $quotation->id);
    }

    public function test_job_card_show_does_not_render_numeric_job_url(): void
    {
        $jobCard = $this->makeJobCard();

        $response = $this->actingAs($this->user)
            ->get(route('admin.production.job-cards.show', $jobCard));

        $response->assertOk();
        $this->assertDoesNotLeakNumericShowUrl($response->getContent(), 'admin/production/job-cards', $jobCard->id);
    }

    public function test_production_queue_page_does_not_render_numeric_job_urls(): void
    {
        $jobCard = $this->makeJobCard();
        $workCenter = WorkCenter::query()->where('company_id', $this->company->id)->firstOrFail();
        app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);

        $response = $this->actingAs($this->user)
            ->get(route('admin.production.queue.index', ['embedded' => 1]));

        $response->assertOk();
        $this->assertDoesNotLeakNumericShowUrl($response->getContent(), 'admin/production/job-cards', $jobCard->id);
    }

    public function test_invoice_and_payment_show_pages_use_hash_urls(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-LEAK-001',
            'invoice_type' => 'standard',
            'invoice_date' => now()->toDateString(),
            'status' => 'draft',
            'subtotal' => 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 100,
            'currency' => 'KES',
            'created_by' => $this->user->id,
        ]);

        $payment = CustomerPayment::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-LEAK-001',
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'amount' => 100,
            'allocated_amount' => 0,
            'unallocated_amount' => 100,
            'currency' => 'KES',
            'status' => CustomerPaymentStatus::Draft,
            'created_by' => $this->user->id,
        ]);

        $invoiceHtml = $this->actingAs($this->user)->get(route('admin.invoices.show', $invoice))->assertOk()->getContent();
        $paymentHtml = $this->actingAs($this->user)->get(route('admin.payments.show', $payment))->assertOk()->getContent();

        $this->assertDoesNotLeakNumericShowUrl($invoiceHtml, 'admin/invoices', $invoice->id);
        $this->assertDoesNotLeakNumericShowUrl($paymentHtml, 'admin/payments', $payment->id);
    }

    public function test_inventory_item_show_does_not_render_numeric_item_url(): void
    {
        $category = \App\Models\Inventory\InventoryCategory::query()->where('company_id', $this->company->id)->firstOrFail();
        $unit = \App\Models\Inventory\UnitOfMeasure::query()->where('company_id', $this->company->id)->firstOrFail();

        $item = InventoryItem::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'inventory_category_id' => $category->id,
            'unit_of_measure_id' => $unit->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.inventory.items.show', $item));

        $response->assertOk();
        $this->assertDoesNotLeakNumericShowUrl($response->getContent(), 'admin/inventory/items', $item->id);
    }

    public function test_queue_presenter_urls_use_public_hashes(): void
    {
        $jobCard = $this->makeJobCard();
        $workCenter = WorkCenter::query()->where('company_id', $this->company->id)->firstOrFail();
        $entry = app(ProductionQueueService::class)->enqueue($jobCard, $workCenter->id, 1);
        $entry->load(['jobCard', 'workCenter']);

        $row = app(\App\Services\Production\ProductionQueueWorkspaceService::class)
            ->presentRow($entry, $this->user);

        $this->assertNotNull($row['job_360_url']);
        $this->assertStringContainsString($jobCard->public_id, $row['job_360_url']);
        $this->assertDoesNotMatchRegularExpression(
            '#/admin/production/job-cards/'.$jobCard->id.'(?:\?|"|\'|$)#',
            $row['job_360_url'],
        );

        if ($row['work_center_url']) {
            $this->assertStringContainsString($workCenter->public_id, $row['work_center_url']);
        }
    }

    public function test_notification_action_urls_use_hashes(): void
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
            'body' => 'Ready.',
            'priority' => NotificationPriority::Normal,
            'subject_type' => Quotation::class,
            'subject_id' => $quotation->id,
            'created_by' => $this->user->id,
        ]);

        $url = app(NotificationService::class)->resolveActionUrl($notification);

        $this->assertNotNull($url);
        $this->assertStringContainsString($quotation->public_id, $url);
        $this->assertStringNotContainsString('/list/'.$quotation->id, $url);
    }

    public function test_audit_command_flags_deliberate_blade_leak_fixture(): void
    {
        $fixture = resource_path('views/testing/public-hash-leak-fixture.blade.php');
        $dir = dirname($fixture);

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($fixture, "{{ route('admin.crm.customers.show', \$customer->id) }}\n");

        try {
            $exitCode = Artisan::call('public-hash:audit', ['--views' => true, '--strict' => true]);
            $this->assertSame(1, $exitCode);
        } finally {
            @unlink($fixture);
        }
    }

    public function test_production_floor_panel_url_uses_public_hash(): void
    {
        $jobCard = $this->makeJobCard();
        $panel = app(\App\Services\Production\ProductionFloorService::class)->panel($jobCard);

        $this->assertStringContainsString($jobCard->public_id, $panel['links']['job']);
        $this->assertStringContainsString($jobCard->public_id, $panel['job']['panel_url']);
    }

    protected function makeSalesOrder(): SalesOrder
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
        ]);

        return SalesOrder::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'created_by' => $this->user->id,
            'status' => SalesOrderStatus::Draft,
        ]);
    }

    protected function makeJobCard(): ProductionJobCard
    {
        $order = $this->makeSalesOrder();

        return ProductionJobCard::factory()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'status' => ProductionJobCardStatus::Draft,
            'created_by' => $this->user->id,
        ]);
    }

    protected function assertDoesNotLeakNumericShowUrl(string $html, string $pathPrefix, int $numericId): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '#/'.$pathPrefix.'/'.$numericId.'(?:\?|"|\'|/|\\\\|$)#',
            $html,
            "Found numeric show URL leak for {$pathPrefix}/{$numericId}",
        );
    }
}
