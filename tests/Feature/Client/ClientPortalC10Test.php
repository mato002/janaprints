<?php

namespace Tests\Feature\Client;

use App\Enums\ClientPortalRepeatRequestStatus;
use App\Enums\CommunicationLogChannel;
use App\Enums\CommunicationLogStatus;
use App\Enums\CommunicationLogType;
use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\FulfilmentStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Client\ClientPortalRepeatRequest;
use App\Models\Communications\CommunicationLog;
use App\Models\Communications\CommunicationRecipient;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Production\ProductionFulfilment;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientPortalC10Test extends TestCase
{
    use RefreshDatabase;

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

    public function test_customer_isolation_blocks_other_customers_order(): void
    {
        $user = $this->clientUser();
        $foreignOrder = SalesOrder::factory()->create([
            'status' => SalesOrderStatus::Confirmed,
        ]);

        $this->actingAsClient($user)
            ->get(route('client.orders.show', $foreignOrder))
            ->assertNotFound();
    }

    public function test_order_visibility_includes_tracking_summary(): void
    {
        $user = $this->clientUser();
        $order = SalesOrder::factory()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => $user->customer_id,
            'status' => SalesOrderStatus::InProduction,
        ]);

        ProductionJobCard::factory()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'status' => ProductionJobCardStatus::InProduction,
        ]);

        $this->actingAsClient($user)
            ->get(route('client.orders.show', $order))
            ->assertOk()
            ->assertSee(__('In Production'), false)
            ->assertSee($order->order_number, false);
    }

    public function test_invoice_visibility_is_scoped_to_customer(): void
    {
        $user = $this->clientUser();

        $invoice = CustomerInvoice::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => $user->customer_id,
            'invoice_number' => 'INV-CLIENT-1001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'currency' => 'KES',
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 1000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'amount_paid' => 0,
            'balance_due' => 1000,
            'created_by' => $user->id,
        ]);

        $foreignInvoice = CustomerInvoice::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => Customer::factory()->create([
                'company_id' => $user->company_id,
                'branch_id' => $user->default_branch_id,
            ])->id,
            'invoice_number' => 'INV-FOREIGN-2002',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'currency' => 'KES',
            'status' => CustomerInvoiceStatus::Posted,
            'subtotal' => 500,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 500,
            'amount_paid' => 0,
            'balance_due' => 500,
            'created_by' => $user->id,
        ]);

        $this->actingAsClient($user)
            ->get(route('client.invoices.index'))
            ->assertOk()
            ->assertSee($invoice->invoice_number, false)
            ->assertDontSee($foreignInvoice->invoice_number, false);
    }

    public function test_statement_visibility_and_download_for_own_customer(): void
    {
        $user = $this->clientUser();

        $this->actingAsClient($user)
            ->get(route('client.statements.index', [
                'from_date' => now()->startOfMonth()->toDateString(),
                'to_date' => now()->toDateString(),
                'preview' => 1,
            ]))
            ->assertOk()
            ->assertSee(__('Statements'), false);

        $this->actingAsClient($user)
            ->get(route('client.statements.download', [
                'from_date' => now()->startOfMonth()->toDateString(),
                'to_date' => now()->toDateString(),
                'format' => 'csv',
            ]))
            ->assertOk();
    }

    public function test_artwork_library_visibility_is_scoped_to_customer(): void
    {
        Storage::fake('local');
        $user = $this->clientUser();

        $path = 'customer-artworks/'.$user->company_id.'/'.$user->customer_id.'/logo.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        $artwork = CustomerArtwork::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => $user->customer_id,
            'artwork_name' => 'Company Logo',
            'artwork_type' => CustomerArtworkType::Logo,
            'version_number' => 1,
            'is_active_version' => true,
            'file_path' => $path,
            'file_name' => 'logo.pdf',
            'mime_type' => 'application/pdf',
            'status' => CustomerArtworkStatus::Active,
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        $foreignArtwork = CustomerArtwork::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => Customer::factory()->create([
                'company_id' => $user->company_id,
                'branch_id' => $user->default_branch_id,
            ])->id,
            'artwork_name' => 'Foreign Logo',
            'artwork_type' => CustomerArtworkType::Logo,
            'version_number' => 1,
            'is_active_version' => true,
            'file_path' => 'customer-artworks/'.$user->company_id.'/foreign/logo.pdf',
            'file_name' => 'foreign.pdf',
            'mime_type' => 'application/pdf',
            'status' => CustomerArtworkStatus::Active,
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        $this->actingAsClient($user)
            ->get(route('client.artwork.index'))
            ->assertOk()
            ->assertSee('Company Logo', false)
            ->assertDontSee('Foreign Logo', false);

        $this->actingAsClient($user)
            ->get(route('client.artwork-library.preview', $artwork))
            ->assertOk();

        $this->actingAsClient($user)
            ->get(route('client.artwork-library.download', $foreignArtwork))
            ->assertNotFound();
    }

    public function test_client_can_upload_artwork_to_library(): void
    {
        Storage::fake('local');
        $user = $this->clientUser();

        $this->actingAsClient($user)
            ->post(route('client.artwork-library.store'), [
                'artwork_name' => 'Brand Guide',
                'file' => \Illuminate\Http\UploadedFile::fake()->create('guide.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('client.artwork.index'));

        $this->assertDatabaseHas('customer_artworks', [
            'customer_id' => $user->customer_id,
            'artwork_name' => 'Brand Guide',
            'is_active_version' => true,
        ]);
    }

    public function test_repeat_order_request_creates_pending_sales_request(): void
    {
        $user = $this->clientUser();
        $order = SalesOrder::factory()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => $user->customer_id,
            'status' => SalesOrderStatus::Delivered,
        ]);

        $this->actingAsClient($user)
            ->post(route('client.repeat-orders.store', $order))
            ->assertRedirect(route('client.repeat-orders.index'));

        $this->assertDatabaseHas('client_portal_repeat_requests', [
            'customer_id' => $user->customer_id,
            'sales_order_id' => $order->id,
            'status' => ClientPortalRepeatRequestStatus::Pending->value,
            'requested_by' => $user->id,
        ]);
    }

    public function test_portal_authentication_blocks_staff_session(): void
    {
        $staff = User::factory()->create();

        $this->withSession(['auth_context' => 'admin'])
            ->actingAs($staff)
            ->get(route('client.dashboard'))
            ->assertRedirect(route('client.login'));

        $this->assertGuest();
    }

    public function test_communication_center_shows_customer_notifications_only(): void
    {
        $user = $this->clientUser();

        $visible = CommunicationLog::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'reference_number' => 'COMM-1001',
            'channel' => CommunicationLogChannel::Email,
            'communication_type' => CommunicationLogType::Transactional,
            'subject' => 'Invoice ready',
            'message_body' => 'Your invoice is ready.',
            'status' => CommunicationLogStatus::Delivered,
            'created_by' => $user->id,
        ]);

        CommunicationRecipient::query()->create([
            'communication_log_id' => $visible->id,
            'recipient_type' => 'customer',
            'recipient_id' => $user->customer_id,
            'display_name' => $user->customer->company_name,
            'email' => $user->email,
            'delivery_status' => CommunicationLogStatus::Delivered,
        ]);

        $hidden = CommunicationLog::query()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'reference_number' => 'COMM-1002',
            'channel' => CommunicationLogChannel::Email,
            'communication_type' => CommunicationLogType::Transactional,
            'subject' => 'Other customer invoice',
            'message_body' => 'Hidden message',
            'status' => CommunicationLogStatus::Delivered,
            'created_by' => $user->id,
        ]);

        CommunicationRecipient::query()->create([
            'communication_log_id' => $hidden->id,
            'recipient_type' => 'customer',
            'recipient_id' => Customer::factory()->create([
                'company_id' => $user->company_id,
                'branch_id' => $user->default_branch_id,
            ])->id,
            'display_name' => 'Other Co',
            'email' => 'other@example.com',
            'delivery_status' => CommunicationLogStatus::Delivered,
        ]);

        $this->actingAsClient($user)
            ->get(route('client.communications.index'))
            ->assertOk()
            ->assertSee('Invoice ready', false)
            ->assertDontSee('Other customer invoice', false);
    }

    public function test_dashboard_reflects_awaiting_collection_metric(): void
    {
        $user = $this->clientUser();
        $order = SalesOrder::factory()->create([
            'company_id' => $user->company_id,
            'branch_id' => $user->default_branch_id,
            'customer_id' => $user->customer_id,
            'status' => SalesOrderStatus::ReadyForDispatch,
        ]);

        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'customer_id' => $order->customer_id,
            'sales_order_id' => $order->id,
            'status' => ProductionJobCardStatus::ReadyForDispatch,
        ]);

        ProductionFulfilment::query()->create([
            'company_id' => $order->company_id,
            'branch_id' => $order->branch_id,
            'production_job_card_id' => $jobCard->id,
            'sales_order_id' => $order->id,
            'fulfilment_method' => \App\Enums\FulfilmentMethod::Collection,
            'status' => FulfilmentStatus::ReadyForCollection,
            'prepared_at' => now(),
        ]);

        $this->actingAsClient($user)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee(__('Awaiting collection'), false);
    }
}
