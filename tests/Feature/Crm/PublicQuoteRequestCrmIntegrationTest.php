<?php

namespace Tests\Feature\Crm;

use App\Enums\LeadStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadSource;
use App\Models\PublicQuoteRequest;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\Platform\SystemSettingsService;
use Database\Seeders\CrmFoundationSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicQuoteRequestCrmIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $salesUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(CrmFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
        $this->salesUser = $this->makeSalesUser();

        Mail::fake();
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_public_quote_submission_creates_crm_lead(): void
    {
        $this->submitQuoteRequest();

        $quoteRequest = PublicQuoteRequest::query()->first();
        $lead = Lead::query()->first();

        $this->assertNotNull($quoteRequest);
        $this->assertNotNull($lead);
        $this->assertSame($lead->id, $quoteRequest->lead_id);
        $this->assertSame($quoteRequest->id, $lead->public_quote_request_id);
        $this->assertSame(LeadStatus::Open, $lead->status);
        $this->assertSame('guest@example.com', $lead->email);
        $this->assertStringContainsString('Business Cards', $lead->notes);
    }

    public function test_lead_is_assigned_to_sales_user(): void
    {
        $this->submitQuoteRequest();

        $quoteRequest = PublicQuoteRequest::query()->first();
        $lead = Lead::query()->first();

        $this->assertSame($this->salesUser->id, $lead->assigned_to);
        $this->assertSame($this->salesUser->id, $quoteRequest->assigned_to);
    }

    public function test_lead_source_matches_storefront_origin(): void
    {
        $this->submitQuoteRequest();

        $lead = Lead::query()->with('leadSource')->first();
        $source = LeadSource::query()
            ->where('company_id', $this->company->id)
            ->where('name', 'Storefront')
            ->first();

        $this->assertNotNull($source);
        $this->assertSame($source->id, $lead->lead_source_id);
    }

    public function test_artwork_is_visible_on_lead_360(): void
    {
        $file = UploadedFile::fake()->create('logo.pdf', 500, 'application/pdf');

        $this->submitQuoteRequest(['artwork' => $file]);

        $lead = Lead::query()->with('publicQuoteRequest')->first();

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);

        $this->actingAs($this->salesUser)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertOk()
            ->assertSee(__('Acquisition intake'), false)
            ->assertSee(__('Storefront'), false)
            ->assertSee('logo.pdf', false)
            ->assertSee(__('Requested product'), false)
            ->assertSee('Business Cards', false);
    }

    public function test_auto_draft_quotation_when_setting_enabled(): void
    {
        app(SystemSettingsService::class)->set(
            'public_quote_auto_draft_quotation',
            true,
            $this->company->id,
            null,
            'boolean',
        );

        $this->submitQuoteRequest();

        $quoteRequest = PublicQuoteRequest::query()->first();
        $lead = Lead::query()->first();
        $quotation = Quotation::query()->where('lead_id', $lead->id)->first();

        $this->assertNotNull($quotation);
        $this->assertSame($quotation->id, $quoteRequest->quotation_id);
        $this->assertNotNull($lead->fresh()->customer_id);
    }

    public function test_auto_create_lead_disabled_skips_crm_bridge(): void
    {
        app(SystemSettingsService::class)->set(
            'public_quote_auto_create_lead',
            false,
            $this->company->id,
            null,
            'boolean',
        );

        $this->submitQuoteRequest();

        $this->assertDatabaseCount('leads', 0);
        $this->assertNull(PublicQuoteRequest::query()->value('lead_id'));
    }

    public function test_quote_request_workspace_shows_linked_lead(): void
    {
        $this->submitQuoteRequest();

        $quoteRequest = PublicQuoteRequest::query()->with('lead')->first();

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);

        $this->actingAs($this->salesUser)
            ->get(route('admin.public-quote-requests.show', $quoteRequest))
            ->assertOk()
            ->assertSee(__('Open CRM Lead'), false)
            ->assertSee(__('CRM lead created'), false);
    }

    public function test_viewer_without_crm_permissions_cannot_open_lead_workspace(): void
    {
        $this->submitQuoteRequest();

        $lead = Lead::query()->first();
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $viewer->assignRole(Role::findByName('Production', 'web'));

        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);

        $this->actingAs($viewer)
            ->get(route('admin.crm.leads.show', $lead))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function submitQuoteRequest(array $overrides = []): void
    {
        $payload = [
            'name' => 'Guest User',
            'company' => 'Acme Ltd',
            'phone' => '+254700000000',
            'email' => 'guest@example.com',
            'service' => 'Business Cards',
            'quantity' => '500',
            'deadline' => 'Next Friday',
            'message' => 'Need premium business cards with matte finish.',
            ...$overrides,
        ];

        $this->post(route('public.quote-requests.store'), $payload)->assertRedirect();
    }

    protected function makeSalesUser(): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions([
            'crm.leads.view', 'crm.leads.create', 'crm.leads.edit',
            'crm.customers.view', 'crm.customers.create',
            'quotations.view', 'quotations.create', 'quotations.edit',
            'public_leads.quote_requests.view', 'public_leads.quote_requests.manage',
        ]);
        $user->assignRole('Sales');

        return $user;
    }
}
