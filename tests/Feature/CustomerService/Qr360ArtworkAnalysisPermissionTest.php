<?php

namespace Tests\Feature\CustomerService;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Qr360ArtworkAnalysisPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('public');
        config(['leads.artwork.disk' => 'public']);
    }

    public function test_cross_company_artwork_cannot_be_analyzed(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $otherCompany = Company::factory()->create();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('Sales', 'web'));

        $quoteRequest = $this->createQuoteRequest($otherCompany->id, $branch->id);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.public-quote-requests.printing-analysis.run', [$quoteRequest, 'primary']))
            ->assertForbidden();
    }

    public function test_user_without_permission_gets_403(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'public_leads.quote_requests.view',
        ]);
        $user->assignRole('Storekeeper');

        $quoteRequest = $this->createQuoteRequest($company->id, $branch->id);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->post(route('admin.public-quote-requests.printing-analysis.run', [$quoteRequest, 'primary']))
            ->assertForbidden();
    }

    protected function createQuoteRequest(int $companyId, int $branchId): PublicQuoteRequest
    {
        $file = UploadedFile::fake()->image('restricted.png', 200, 200);
        $path = $file->store('quote-artwork/2026/06', 'public');

        return PublicQuoteRequest::query()->create([
            'name' => 'Restricted Client',
            'phone' => '+254700000077',
            'email' => 'restricted@example.com',
            'service_needed' => 'Banners',
            'message' => 'Banner artwork',
            'artwork_path' => $path,
            'artwork_original_name' => 'restricted.png',
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);
    }
}
