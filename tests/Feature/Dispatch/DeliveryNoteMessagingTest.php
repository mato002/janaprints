<?php

namespace Tests\Feature\Dispatch;

use App\Enums\CustomerInvoiceStatus;
use App\Models\Sales\CustomerInvoice;
use App\Services\Accounting\DeliveryInvoiceEligibilityService;
use App\Services\Dispatch\DeliveryNoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Dispatch\Concerns\InteractsWithDispatchInventory;
use Tests\TestCase;

class DeliveryNoteMessagingTest extends TestCase
{
    use InteractsWithDispatchInventory;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedDispatchInventoryEnvironment();
    }

    public function test_commercial_billing_notes_describe_advance_invoice_without_blocking_dispatch(): void
    {
        [$note, , $user, $jobCard] = $this->prepareDraftNoteWithFg();
        $order = $jobCard->salesOrder;

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $note->company_id,
            'branch_id' => $note->branch_id,
            'customer_id' => $note->customer_id,
            'sales_order_id' => $order->id,
            'delivery_note_id' => null,
            'invoice_number' => 'DEMO-INV-ADV-01',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 1000,
            'tax_amount' => 160,
            'total_amount' => 1160,
            'status' => CustomerInvoiceStatus::Approved,
            'created_by' => $user->id,
        ]);

        $notes = app(DeliveryInvoiceEligibilityService::class)->commercialBillingNotes($note->fresh(['salesOrder']));

        $this->assertCount(1, $notes);
        $this->assertStringContainsString($invoice->invoice_number, $notes[0]);
        $this->assertStringContainsString('does not block dispatch', $notes[0]);
    }

    public function test_dispatch_step_hides_invoice_generation_blockers_on_show_page(): void
    {
        [$note, , $user, $jobCard] = $this->prepareDraftNoteWithFg();
        app(DeliveryNoteService::class)->markPackaged($note, $user->id, ['package_count' => 1]);

        CustomerInvoice::query()->create([
            'company_id' => $note->company_id,
            'branch_id' => $note->branch_id,
            'customer_id' => $note->customer_id,
            'sales_order_id' => $jobCard->sales_order_id,
            'delivery_note_id' => null,
            'invoice_number' => 'DEMO-INV-0003',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 500,
            'tax_amount' => 80,
            'total_amount' => 580,
            'status' => CustomerInvoiceStatus::Approved,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.dispatch.delivery-notes.show', $note));

        $response->assertOk();
        $response->assertSee(__('Commercial billing'), false);
        $response->assertSee('DEMO-INV-0003', false);
        $response->assertSee('does not block dispatch', false);
        $response->assertDontSee(__('Delivery note must be in delivered status.'), false);
        $response->assertDontSee(__('Delivery note is not marked invoice-ready.'), false);
        $response->assertDontSee(__('not linked here'), false);
    }
}
