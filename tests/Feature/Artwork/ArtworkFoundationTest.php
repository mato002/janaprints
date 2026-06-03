<?php

namespace Tests\Feature\Artwork;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtworkFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
    }

    public function test_company_isolation_for_artwork_requests(): void
    {
        $companyA = Company::factory()->create(['code' => 'AA']);
        $companyB = Company::factory()->create(['code' => 'AB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id, 'code' => 'BA']);
        $branchB = Branch::factory()->create(['company_id' => $companyB->id, 'code' => 'BB']);
        $customerB = Customer::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'customer_code' => 'C-ART-1',
            'company_name' => 'Other Co',
            'status' => CustomerStatus::Active,
        ]);

        $userA = $this->artworkUser($companyA, $branchA, ['artwork.view']);
        $requestB = ArtworkRequest::factory()->create([
            'company_id' => $companyB->id,
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
        ]);

        $this->actingAs($userA)
            ->get(route('admin.artwork.show', $requestB))
            ->assertForbidden();
    }

    public function test_viewer_cannot_create_artwork_request(): void
    {
        [, , , $user] = $this->artworkContext(['artwork.view']);

        $this->actingAs($user)
            ->get(route('admin.artwork.create'))
            ->assertForbidden();
    }

    public function test_version_creation_increments_without_overwriting(): void
    {
        [$company, $branch, $customer, $user] = $this->artworkContext([
            'artwork.view', 'artwork.create', 'artwork.edit', 'artwork.assign', 'artwork.submit',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $artworkRequest = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::InDesign,
            'current_version' => 0,
        ]);

        $file = UploadedFile::fake()->create('design-v1.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('admin.artwork.versions.store', $artworkRequest), [
                'file' => $file,
                'notes' => 'First draft',
            ])
            ->assertRedirect();

        $artworkRequest->refresh();
        $this->assertEquals(1, $artworkRequest->current_version);
        $this->assertDatabaseHas('artwork_versions', [
            'artwork_request_id' => $artworkRequest->id,
            'version_number' => 1,
            'notes' => 'First draft',
        ]);

        $file2 = UploadedFile::fake()->create('design-v2.pdf', 100, 'application/pdf');

        $this->actingAs($user)
            ->post(route('admin.artwork.versions.store', $artworkRequest), ['file' => $file2])
            ->assertRedirect();

        $artworkRequest->refresh();
        $this->assertEquals(2, $artworkRequest->current_version);
        $this->assertEquals(2, ArtworkVersion::query()->where('artwork_request_id', $artworkRequest->id)->count());
    }

    public function test_approval_workflow_with_revision_cycle(): void
    {
        [$company, $branch, $customer, $sales] = $this->artworkContext([
            'artwork.view', 'artwork.create', 'artwork.edit', 'artwork.assign',
            'artwork.submit', 'artwork.approve',
        ]);

        $designer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $designerRole = Role::findByName('Designer', 'web');
        $designerRole->syncPermissions(['artwork.view', 'artwork.edit', 'artwork.submit']);
        $designer->assignRole('Designer');

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $artworkRequest = ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'requested_by' => $sales->id,
            'status' => ArtworkRequestStatus::InDesign,
            'assigned_designer_id' => $designer->id,
            'current_version' => 1,
        ]);

        ArtworkVersion::query()->create([
            'artwork_request_id' => $artworkRequest->id,
            'version_number' => 1,
            'file_path' => 'artwork/test/v1.pdf',
            'original_name' => 'v1.pdf',
            'uploaded_by' => $designer->id,
        ]);

        $this->actingAs($designer)
            ->post(route('admin.artwork.submit', $artworkRequest))
            ->assertRedirect();

        $artworkRequest->refresh();
        $this->assertEquals(ArtworkRequestStatus::Submitted, $artworkRequest->status);

        $this->actingAs($sales)
            ->post(route('admin.artwork.approve', $artworkRequest), [
                'decision' => ArtworkApprovalDecision::RevisionRequested->value,
                'comments' => 'Adjust logo size',
            ])
            ->assertRedirect();

        $artworkRequest->refresh();
        $this->assertEquals(ArtworkRequestStatus::RevisionRequested, $artworkRequest->status);
        $this->assertDatabaseHas('artwork_approvals', [
            'artwork_request_id' => $artworkRequest->id,
            'decision' => ArtworkApprovalDecision::RevisionRequested->value,
        ]);

        $this->actingAs($sales)
            ->post(route('admin.artwork.start-design', $artworkRequest))
            ->assertRedirect();

        $artworkRequest->refresh();
        $this->assertEquals(ArtworkRequestStatus::InDesign, $artworkRequest->status);

        $this->actingAs($designer)
            ->post(route('admin.artwork.submit', $artworkRequest))
            ->assertRedirect();

        $this->actingAs($sales)
            ->post(route('admin.artwork.approve', $artworkRequest), [
                'decision' => ArtworkApprovalDecision::Approved->value,
            ])
            ->assertRedirect();

        $artworkRequest->refresh();
        $this->assertEquals(ArtworkRequestStatus::Approved, $artworkRequest->status);
        $this->assertEquals(2, ArtworkApproval::query()->where('artwork_request_id', $artworkRequest->id)->count());
    }

    /**
     * @return array{0: Company, 1: Branch, 2: Customer, 3: User}
     */
    protected function artworkContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_code' => 'CUST-ART-01',
            'company_name' => 'Artwork Customer',
            'status' => CustomerStatus::Active,
        ]);
        $permissions ??= ['artwork.view', 'artwork.create', 'artwork.edit'];
        $user = $this->artworkUser($company, $branch, $permissions);

        return [$company, $branch, $customer, $user];
    }

    protected function artworkUser(Company $company, Branch $branch, array $permissions): User
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
