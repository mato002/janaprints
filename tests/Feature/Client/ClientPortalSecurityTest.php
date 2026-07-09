<?php

namespace Tests\Feature\Client;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Crm\Customer;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_client_cannot_view_another_customers_quotation(): void
    {
        [$owner, $other] = $this->twoClients();

        $quotation = Quotation::factory()->create([
            'company_id' => $owner->customer->company_id,
            'branch_id' => $owner->customer->branch_id,
            'customer_id' => $owner->customer_id,
            'prepared_by' => $owner->id,
            'status' => QuotationStatus::Sent,
        ]);

        $this->actingAsClient($other)
            ->get(route('client.quotations.show', $quotation))
            ->assertNotFound();
    }

    public function test_client_cannot_view_another_customers_invoice_or_pdf(): void
    {
        [$owner, $other] = $this->twoClients();

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $owner->customer->company_id,
            'branch_id' => $owner->customer->branch_id,
            'customer_id' => $owner->customer_id,
            'invoice_number' => 'INV-SEC-001',
            'invoice_type' => CustomerInvoiceType::Standard,
            'invoice_date' => now()->toDateString(),
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'currency' => 'KES',
            'created_by' => $owner->id,
        ]);

        $this->actingAsClient($other)->get(route('client.invoices.show', $invoice))->assertNotFound();
        $this->actingAsClient($other)->get(route('client.invoices.pdf', $invoice))->assertNotFound();
    }

    public function test_client_cannot_view_another_customers_job(): void
    {
        [$owner, $other] = $this->twoClients();

        $order = SalesOrder::factory()->create([
            'company_id' => $owner->customer->company_id,
            'branch_id' => $owner->customer->branch_id,
            'customer_id' => $owner->customer_id,
            'status' => SalesOrderStatus::InProduction,
            'created_by' => $owner->id,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $owner->customer->company_id,
            'branch_id' => $owner->customer->branch_id,
            'customer_id' => $owner->customer_id,
            'sales_order_id' => $order->id,
            'status' => ProductionJobCardStatus::InProduction,
            'created_by' => $owner->id,
        ]);

        $this->actingAsClient($other)->get(route('client.jobs.show', $jobCard))->assertNotFound();
    }

    public function test_client_sees_own_jobs_and_communications_history(): void
    {
        $owner = $this->clientUser();

        $this->actingAsClient($owner)->get(route('client.jobs.index'))->assertOk();
        $this->actingAsClient($owner)->get(route('client.communications.history'))->assertOk();
    }

    /**
     * @return array{0: User, 1: User}
     */
    protected function twoClients(): array
    {
        return [$this->clientUser(), $this->clientUser()];
    }

    protected function clientUser(?Customer $customer = null): User
    {
        $customer ??= Customer::factory()->create();

        return User::factory()->create([
            'company_id' => $customer->company_id,
            'default_branch_id' => $customer->branch_id,
            'customer_id' => $customer->id,
            'employee_id' => null,
        ]);
    }

    protected function actingAsClient(User $user)
    {
        return $this->withSession(['auth_context' => 'client'])->actingAs($user);
    }
}
