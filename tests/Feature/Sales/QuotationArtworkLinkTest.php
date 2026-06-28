<?php

namespace Tests\Feature\Sales;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Enums\CustomerStatus;
use App\Enums\QuotationStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Sales\Quotation;
use App\Models\User;
use App\Support\ArtworkApprovalValidator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotationArtworkLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
    }

    public function test_user_can_link_customer_library_artwork_to_quotation(): void
    {
        [$company, $branch, $customer, $user, $quotation, $libraryArtwork] = $this->context();

        Storage::disk('local')->put($libraryArtwork->file_path, 'fake-pdf-content');

        $this->actingAs($user)
            ->post(route('admin.quotations.link-artwork', $quotation), [
                'artwork_source' => 'library',
                'customer_artwork_id' => $libraryArtwork->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $request = ArtworkRequest::query()->where('quotation_id', $quotation->id)->first();
        $this->assertNotNull($request);
        $this->assertEquals(ArtworkRequestStatus::Approved, $request->status);
        $this->assertEquals(1, $request->current_version);
        $this->assertDatabaseHas('artwork_versions', ['artwork_request_id' => $request->id, 'version_number' => 1]);
        $this->assertDatabaseHas('artwork_approvals', [
            'artwork_request_id' => $request->id,
            'decision' => ArtworkApprovalDecision::Approved->value,
        ]);

        app(ArtworkApprovalValidator::class)->assertCanCreateFromQuotation($quotation->fresh());
    }

    public function test_user_can_link_existing_approved_artwork_request(): void
    {
        [$company, $branch, $customer, $user, $quotation] = $this->context(withLibrary: false);

        $existing = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Approved,
            'current_version' => 1,
            'quotation_id' => null,
        ]);

        $version = ArtworkVersion::query()->create([
            'artwork_request_id' => $existing->id,
            'version_number' => 1,
            'file_path' => 'artwork/test.pdf',
            'original_name' => 'test.pdf',
            'uploaded_by' => $user->id,
        ]);

        ArtworkApproval::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'artwork_request_id' => $existing->id,
            'artwork_version_id' => $version->id,
            'approved_by' => $user->id,
            'decision' => ArtworkApprovalDecision::Approved,
        ]);

        $this->actingAs($user)
            ->post(route('admin.quotations.link-artwork', $quotation), [
                'artwork_source' => 'request',
                'artwork_request_id' => $existing->id,
            ])
            ->assertRedirect();

        $this->assertEquals($quotation->id, $existing->fresh()->quotation_id);
        app(ArtworkApprovalValidator::class)->assertCanCreateFromQuotation($quotation->fresh());
    }

    public function test_quotation_show_displays_artwork_link_panel(): void
    {
        [$company, $branch, $customer, $user, $quotation, $libraryArtwork] = $this->context();

        $this->actingAs($user)
            ->get(route('admin.quotations.show', $quotation))
            ->assertOk()
            ->assertSee(__('Link artwork'))
            ->assertSee($libraryArtwork->artwork_name);
    }

    public function test_quotation_store_can_optionally_link_customer_artwork(): void
    {
        [$company, $branch, $customer, $user, $quotation, $libraryArtwork] = $this->context();

        Storage::disk('local')->put($libraryArtwork->file_path, 'fake-pdf-content');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.quotations.store'), [
                'customer_id' => $customer->id,
                'quotation_date' => now()->toDateString(),
                'currency' => 'KES',
                'customer_artwork_id' => $libraryArtwork->id,
                'items' => [
                    [
                        'item_type' => 'product',
                        'item_name' => 'A5 Flyers',
                        'quantity' => 10,
                        'unit_price' => 500,
                        'discount' => 0,
                        'tax_rate' => 16,
                    ],
                ],
            ])
            ->assertRedirect();

        $created = Quotation::query()->where('customer_id', $customer->id)->latest('id')->first();
        $this->assertNotNull($created);
        $this->assertDatabaseHas('artwork_requests', [
            'quotation_id' => $created->id,
            'customer_id' => $customer->id,
            'status' => ArtworkRequestStatus::Approved->value,
        ]);
    }

    public function test_customer_artworks_endpoint_returns_library_options(): void
    {
        [$company, $branch, $customer, $user, $quotation, $libraryArtwork] = $this->context();

        $this->actingAs($user)
            ->getJson(route('admin.quotations.customer-artworks', $customer))
            ->assertOk()
            ->assertJsonPath('artworks.0.id', $libraryArtwork->id)
            ->assertJsonFragment(['label' => $libraryArtwork->artwork_name.' · '.$libraryArtwork->versionLabel()]);
    }

    public function test_customer_artwork_preview_endpoint_returns_file(): void
    {
        [$company, $branch, $customer, $user, , $libraryArtwork] = $this->context();

        Storage::disk('local')->put($libraryArtwork->file_path, 'fake-image-bytes');

        $this->actingAs($user)
            ->get(route('admin.crm.customers.artworks.preview', [
                'customer' => $customer,
                'customerArtwork' => $libraryArtwork,
            ]))
            ->assertOk();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User, 4: Quotation, 5: ?CustomerArtwork}
     */
    protected function context(bool $withLibrary = true): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-ART-01',
            'company_name' => 'Art Customer',
            'status' => CustomerStatus::Active,
        ]);
        $user = $this->salesUser($company, $branch, [
            'quotations.view', 'quotations.edit', 'quotations.convert', 'quotations.create',
            'crm.customers.view',
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'prepared_by' => $user->id,
            'status' => QuotationStatus::Accepted,
            'revision_number' => 1,
        ]);

        $libraryArtwork = null;
        if ($withLibrary) {
            $libraryArtwork = CustomerArtwork::query()->create([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'artwork_name' => 'Flyer Layout',
                'artwork_type' => CustomerArtworkType::Layout,
                'version_number' => 1,
                'is_active_version' => true,
                'file_path' => 'customer-artworks/flyer.pdf',
                'file_name' => 'flyer.pdf',
                'mime_type' => 'application/pdf',
                'status' => CustomerArtworkStatus::Active,
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
            ]);
        }

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$company, $branch, $customer, $user, $quotation, $libraryArtwork];
    }

    protected function salesUser(Company $company, Branch $branch, array $permissions): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Sales', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Sales');

        return $user;
    }
}
