<?php

namespace Tests\Feature\Storefront;

use App\Mail\PublicContactMessageConfirmationMail;
use App\Mail\PublicContactMessageInternalNotificationMail;
use App\Mail\PublicQuoteRequestConfirmationMail;
use App\Mail\PublicQuoteRequestInternalNotificationMail;
use App\Models\Branch;
use App\Models\Company;
use App\Enums\NotificationType;
use App\Enums\PublicQuoteRequestStatus;
use App\Models\Communications\ErpNotification;
use App\Models\PublicContactMessage;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicLeadsFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        config(['leads.admin_email' => 'leads@janaprints.test']);
        Storage::fake('public');
    }

    public function test_guest_can_submit_quote_request(): void
    {
        Mail::fake();

        $response = $this->post(route('public.quote-requests.store'), $this->validQuotePayload());

        $response->assertRedirect();
        $response->assertSessionHas('quote_success');
        $this->assertDatabaseHas('public_quote_requests', [
            'email' => 'guest@example.com',
            'service_needed' => 'Business Cards',
        ]);
    }

    public function test_guest_can_submit_contact_message(): void
    {
        Mail::fake();

        $response = $this->post(route('public.contact-messages.store'), $this->validContactPayload());

        $response->assertRedirect();
        $response->assertSessionHas('contact_success');
        $this->assertDatabaseHas('public_contact_messages', [
            'email' => 'guest@example.com',
            'subject' => 'Delivery enquiry',
        ]);
    }

    public function test_validation_rejects_invalid_quote_request(): void
    {
        $response = $this->post(route('public.quote-requests.store'), [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'service_needed', 'message']);
    }

    public function test_validation_rejects_invalid_contact_message(): void
    {
        $response = $this->post(route('public.contact-messages.store'), [
            'name' => '',
            'email' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }

    public function test_artwork_upload_validates_file_type_and_size(): void
    {
        $invalid = UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream');

        $response = $this->post(route('public.quote-requests.store'), [
            ...$this->validQuotePayload(),
            'artwork' => $invalid,
        ]);

        $response->assertSessionHasErrors('artwork');

        $tooLarge = UploadedFile::fake()->create('large.pdf', 30000, 'application/pdf');

        $response = $this->post(route('public.quote-requests.store'), [
            ...$this->validQuotePayload(),
            'artwork' => $tooLarge,
        ]);

        $response->assertSessionHasErrors('artwork');
    }

    public function test_quote_request_is_stored_with_artwork(): void
    {
        Mail::fake();

        $file = UploadedFile::fake()->create('logo.pdf', 500, 'application/pdf');

        $this->post(route('public.quote-requests.store'), [
            ...$this->validQuotePayload(),
            'artwork' => $file,
        ])->assertRedirect();

        $quoteRequest = PublicQuoteRequest::query()->first();

        $this->assertNotNull($quoteRequest);
        $this->assertNotNull($quoteRequest->artwork_path);
        Storage::disk('public')->assertExists($quoteRequest->artwork_path);
    }

    public function test_contact_message_is_stored(): void
    {
        Mail::fake();

        $this->post(route('public.contact-messages.store'), $this->validContactPayload());

        $this->assertDatabaseCount('public_contact_messages', 1);
    }

    public function test_quote_confirmation_email_is_queued(): void
    {
        Mail::fake();

        $this->post(route('public.quote-requests.store'), $this->validQuotePayload());

        Mail::assertSent(PublicQuoteRequestConfirmationMail::class, function ($mail) {
            return $mail->hasTo('guest@example.com');
        });
    }

    public function test_contact_confirmation_email_is_queued(): void
    {
        Mail::fake();

        $this->post(route('public.contact-messages.store'), $this->validContactPayload());

        Mail::assertSent(PublicContactMessageConfirmationMail::class, function ($mail) {
            return $mail->hasTo('guest@example.com');
        });
    }

    public function test_internal_notification_emails_are_queued(): void
    {
        Mail::fake();

        $this->post(route('public.quote-requests.store'), $this->validQuotePayload());
        $this->post(route('public.contact-messages.store'), $this->validContactPayload());

        Mail::assertSent(PublicQuoteRequestInternalNotificationMail::class, function ($mail) {
            return $mail->hasTo('leads@janaprints.test');
        });

        Mail::assertSent(PublicContactMessageInternalNotificationMail::class, function ($mail) {
            return $mail->hasTo('leads@janaprints.test');
        });
    }

    public function test_admin_can_view_quote_requests(): void
    {
        $user = $this->adminUser();

        PublicQuoteRequest::query()->create([
            'name' => 'Jane Doe',
            'phone' => '+254700000001',
            'email' => 'jane@example.com',
            'service_needed' => 'Brochures',
            'message' => 'Need 1000 brochures',
        ]);

        $this->actingAs($user)
            ->get(route('admin.public-quote-requests.index'))
            ->assertOk()
            ->assertSee('Jane Doe');
    }

    public function test_admin_can_view_contact_messages(): void
    {
        $user = $this->adminUser();

        PublicContactMessage::query()->create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'subject' => 'Pricing question',
            'message' => 'Please call me back.',
        ]);

        $this->actingAs($user)
            ->get(route('admin.public-contact-messages.index'))
            ->assertOk()
            ->assertSee('John Smith');
    }

    public function test_status_updates_work_for_quote_requests(): void
    {
        $user = $this->adminUser();

        $quoteRequest = PublicQuoteRequest::query()->create([
            'name' => 'Jane Doe',
            'phone' => '+254700000001',
            'email' => 'jane@example.com',
            'service_needed' => 'Brochures',
            'message' => 'Need brochures',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.public-quote-requests.update-status', $quoteRequest), [
                'status' => 'reviewing',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('public_quote_requests', [
            'id' => $quoteRequest->id,
            'status' => 'reviewing',
        ]);
    }

    public function test_admin_can_view_quote_request_workspace(): void
    {
        $user = $this->adminUser();

        $quoteRequest = PublicQuoteRequest::query()->create([
            'name' => 'James Ngotho',
            'company' => 'Prady Technologies Ltd',
            'phone' => '+254700000001',
            'email' => 'james@example.com',
            'service_needed' => 'Large Format / Banners',
            'quantity' => '500',
            'message' => 'Make it look good.',
        ]);

        $this->actingAs($user)
            ->get(route('admin.public-quote-requests.show', $quoteRequest))
            ->assertOk()
            ->assertSee('QR-0001')
            ->assertSee('Artwork Review')
            ->assertSee('Sales Review')
            ->assertSee('Customer & Request Snapshot')
            ->assertSee('Next Recommended Action')
            ->assertSee('James Ngotho');
    }

    public function test_admin_can_save_commercial_review_and_add_note(): void
    {
        $user = $this->adminUser();

        $quoteRequest = PublicQuoteRequest::query()->create([
            'name' => 'Jane Doe',
            'phone' => '+254700000001',
            'email' => 'jane@example.com',
            'service_needed' => 'Brochures',
            'message' => 'Need brochures',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.public-quote-requests.update-review', $quoteRequest), [
                'status' => 'reviewing',
                'priority' => 'high',
                'expected_value' => 15000,
                'probability' => 60,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('public_quote_requests', [
            'id' => $quoteRequest->id,
            'status' => 'reviewing',
            'priority' => 'high',
            'expected_value' => '15000.00',
            'probability' => 60,
        ]);

        $this->actingAs($user)
            ->post(route('admin.public-quote-requests.notes.store', $quoteRequest), [
                'body' => 'Called customer. Awaiting final dimensions.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('public_quote_request_notes', [
            'public_quote_request_id' => $quoteRequest->id,
            'user_id' => $user->id,
            'body' => 'Called customer. Awaiting final dimensions.',
        ]);
    }

    public function test_admin_can_preview_uploaded_artwork(): void
    {
        $user = $this->adminUser();

        $path = 'public-quote-artwork/test-logo.png';
        Storage::disk('public')->put($path, 'fake-image-content');

        $quoteRequest = PublicQuoteRequest::query()->create([
            'name' => 'Jane Doe',
            'phone' => '+254700000001',
            'email' => 'jane@example.com',
            'service_needed' => 'Brochures',
            'message' => 'Need brochures',
            'artwork_path' => $path,
            'artwork_original_name' => 'logo.png',
        ]);

        $this->actingAs($user)
            ->get(route('admin.public-quote-requests.artwork-preview', $quoteRequest))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.public-quote-requests.artwork', $quoteRequest))
            ->assertOk();
    }

    public function test_dashboard_shows_new_quote_requests_alert_with_pending_count(): void
    {
        $user = $this->adminUser();

        PublicQuoteRequest::query()->create([
            'name' => 'Jane Doe',
            'phone' => '+254700000001',
            'email' => 'jane@example.com',
            'service_needed' => 'Brochures',
            'message' => 'Need brochures',
            'status' => PublicQuoteRequestStatus::Pending,
        ]);

        PublicQuoteRequest::query()->create([
            'name' => 'John Smith',
            'phone' => '+254700000002',
            'email' => 'john@example.com',
            'service_needed' => 'Banners',
            'message' => 'Need banners',
            'status' => PublicQuoteRequestStatus::Quoted,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('New Quote Requests')
            ->assertSee('Review Requests')
            ->assertSee('Sales Opportunities');
    }

    public function test_topbar_shows_quote_requests_badge_for_authorized_users(): void
    {
        $user = $this->adminUser();

        PublicQuoteRequest::query()->create([
            'name' => 'Jane Doe',
            'phone' => '+254700000001',
            'email' => 'jane@example.com',
            'service_needed' => 'Brochures',
            'message' => 'Need brochures',
            'status' => PublicQuoteRequestStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('admin.public-quote-requests.index'))
            ->assertOk()
            ->assertSee('erp-quote-topbar-btn', false)
            ->assertSee('Quote Requests');
    }

    public function test_topbar_quote_requests_link_hidden_without_permission(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('Production', 'web'));

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        PublicQuoteRequest::query()->create([
            'name' => 'Jane Doe',
            'phone' => '+254700000001',
            'email' => 'jane@example.com',
            'service_needed' => 'Brochures',
            'message' => 'Need brochures',
            'status' => PublicQuoteRequestStatus::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('erp-quote-topbar-btn', false);
    }

    public function test_quoted_and_rejected_requests_do_not_count_as_pending(): void
    {
        $user = $this->adminUser();

        PublicQuoteRequest::query()->create([
            'name' => 'Quoted Lead',
            'phone' => '+254700000001',
            'email' => 'quoted@example.com',
            'service_needed' => 'Cards',
            'message' => 'Quoted',
            'status' => PublicQuoteRequestStatus::Quoted,
        ]);

        PublicQuoteRequest::query()->create([
            'name' => 'Rejected Lead',
            'phone' => '+254700000002',
            'email' => 'rejected@example.com',
            'service_needed' => 'Cards',
            'message' => 'Spam',
            'status' => PublicQuoteRequestStatus::Spam,
        ]);

        $count = app(\App\Services\Commercial\PublicQuoteRequestCountService::class)->pendingCount();

        $this->assertSame(0, $count);
    }

    public function test_submitting_quote_request_creates_internal_notification_without_duplicates(): void
    {
        Mail::fake();

        $admin = $this->adminUser();

        $this->post(route('public.quote-requests.store'), $this->validQuotePayload());

        $quoteRequest = PublicQuoteRequest::query()->first();
        $this->assertNotNull($quoteRequest);

        $this->assertDatabaseHas('notifications', [
            'recipient_user_id' => $admin->id,
            'type' => NotificationType::PublicQuoteRequestReceived->value,
            'subject_type' => PublicQuoteRequest::class,
            'subject_id' => $quoteRequest->id,
        ]);

        $before = ErpNotification::query()->count();

        app(\App\Services\Commercial\PublicQuoteRequestNotificationService::class)
            ->notifyNewRequest($quoteRequest);

        $this->assertSame($before, ErpNotification::query()->count());
    }

    public function test_unauthorized_users_cannot_access_admin_inboxes(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('Production', 'web'));

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.public-quote-requests.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.public-contact-messages.index'))
            ->assertForbidden();
    }

    /**
     * @return array<string, string>
     */
    protected function validQuotePayload(): array
    {
        return [
            'name' => 'Guest User',
            'company' => 'Acme Ltd',
            'phone' => '+254700000000',
            'email' => 'guest@example.com',
            'service' => 'Business Cards',
            'quantity' => '500',
            'deadline' => 'Next Friday',
            'message' => 'Need premium business cards with matte finish.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validContactPayload(): array
    {
        return [
            'name' => 'Guest User',
            'company' => 'Acme Ltd',
            'phone' => '+254700000000',
            'email' => 'guest@example.com',
            'subject' => 'Delivery enquiry',
            'message' => 'Do you deliver to Mombasa?',
        ];
    }

    protected function adminUser(): User
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole(Role::findByName('Sales', 'web'));

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return $user;
    }
}
