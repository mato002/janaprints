<?php

namespace Tests\Feature\Dispatch;

use App\Enums\CustomerInvoiceStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrderItem;
use App\Services\Dispatch\DeliveryNoteService;
use App\Support\Sales\CustomerInvoiceService;
use App\Support\TenantContext;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\JanaPrintsTaxSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class DeliveryNoteInvoiceTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);
        $this->seed(JanaPrintsTaxSeeder::class);
    }

    public function test_generate_invoice_from_delivered_delivery_note(): void
    {
        [$note, , $user, $jobCard] = $this->readyDispatchedDeliveryNote();
        $order = $jobCard->salesOrder;

        SalesOrderItem::query()->create([
            'sales_order_id' => $order->id,
            'item_name' => 'Cover page print',
            'description' => 'cover page',
            'quantity' => 100,
            'unit_price' => 50,
            'line_total' => 5000,
            'sort_order' => 1,
        ]);
        $order->update([
            'subtotal' => 5000,
            'tax_amount' => 800,
            'total_amount' => 5800,
        ]);

        app(DeliveryNoteService::class)->deliver($note->fresh(), $user->id, [
            'recipient_name' => 'Customer Rep',
        ]);
        $note = $note->fresh(['items', 'salesOrder']);

        $admin = $user;
        $admin->assignRole('Company Admin');
        $company = Company::query()->findOrFail($jobCard->company_id);
        $branch = Branch::query()->findOrFail($jobCard->branch_id);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($admin)
            ->post(route('admin.dispatch.delivery-notes.generate-invoice', $note));

        $invoice = CustomerInvoice::query()->where('delivery_note_id', $note->id)->first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertSame($note->id, $invoice->delivery_note_id);
        $this->assertSame($order->id, $invoice->sales_order_id);
        $this->assertNotNull($note->fresh()->invoiced_at);
        $this->assertSame(CustomerInvoiceStatus::Approved, $invoice->status);
    }

    public function test_blocks_invoice_when_sales_order_fully_invoiced(): void
    {
        [$note, , $user, $jobCard] = $this->readyDispatchedDeliveryNote();
        $order = $jobCard->salesOrder;

        SalesOrderItem::query()->create([
            'sales_order_id' => $order->id,
            'item_name' => 'Cover page print',
            'quantity' => 100,
            'unit_price' => 50,
            'line_total' => 5000,
            'sort_order' => 1,
        ]);
        $order->update([
            'subtotal' => 5000,
            'tax_amount' => 800,
            'total_amount' => 5800,
        ]);

        app(DeliveryNoteService::class)->deliver($note->fresh(), $user->id);
        $note = $note->fresh(['items', 'salesOrder']);

        $admin = $user;
        $admin->assignRole('Company Admin');
        $company = Company::query()->findOrFail($jobCard->company_id);
        $branch = Branch::query()->findOrFail($jobCard->branch_id);
        app()->instance(TenantContext::class, new TenantContext($company, $branch, false));
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        app(CustomerInvoiceService::class)->createFromSalesOrder($order->fresh(), $admin->id);

        $response = $this->actingAs($admin)
            ->post(route('admin.dispatch.delivery-notes.generate-invoice', $note));

        $response->assertSessionHasErrors('delivery_note');
        $this->assertNull(CustomerInvoice::query()->where('delivery_note_id', $note->id)->first());
    }
}
