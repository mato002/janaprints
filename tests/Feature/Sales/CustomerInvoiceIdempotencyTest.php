<?php

namespace Tests\Feature\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\CustomerStatus;
use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Support\Sales\CustomerInvoiceCreationAuthority;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerInvoiceIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_standard_invoice_creation_returns_existing_open_invoice_for_sales_order(): void
    {
        [$user, $order, $existing] = $this->context();

        $result = app(CustomerInvoiceCreationAuthority::class)->createFromSalesOrder($order, $user->id, [
            'invoice_type' => CustomerInvoiceType::Standard,
        ]);

        $this->assertTrue($result->wasExisting);
        $this->assertSame($existing->id, $result->invoice->id);
    }

    public function test_delivery_note_invoice_creation_is_idempotent(): void
    {
        [$user, $order, $existing] = $this->context();

        $note = DeliveryNote::query()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'delivery_note_number' => 'DN-TEST-001',
            'delivery_date' => now()->toDateString(),
            'status' => DeliveryNoteStatus::Draft,
        ]);

        CustomerInvoice::query()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'delivery_note_id' => $note->id,
            'invoice_number' => 'INV-DN-001',
            'invoice_type' => CustomerInvoiceType::Standard,
            'invoice_date' => now()->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'currency' => 'KES',
            'created_by' => $user->id,
        ]);

        $authority = app(CustomerInvoiceCreationAuthority::class);
        $result = $authority->createFromDeliveryNote($note->fresh(), $user->id);

        $this->assertTrue($result->wasExisting);
    }

    /**
     * @return array{0: User, 1: SalesOrder, 2?: CustomerInvoice}
     */
    protected function context(bool $withExistingInvoice = true): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => CustomerStatus::Active,
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::create(['name' => 'Invoice Idempotency '.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(['invoices.create', 'invoices.view', 'sales_orders.view', 'sales_orders.production']);
        $user->assignRole($role);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Converted,
        ]);

        $order = SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'status' => SalesOrderStatus::Confirmed,
            'created_by' => $user->id,
        ]);

        if (! $withExistingInvoice) {
            return [$user, $order];
        }

        $existing = CustomerInvoice::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sales_order_id' => $order->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_type' => CustomerInvoiceType::Standard,
            'invoice_date' => now()->toDateString(),
            'status' => CustomerInvoiceStatus::Draft,
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'currency' => 'KES',
            'created_by' => $user->id,
        ]);

        return [$user, $order, $existing];
    }
}
