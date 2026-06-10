<?php

namespace Tests\Feature\CustomerService;

use App\Models\Branch;
use App\Models\Company;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use App\Services\PrintingIntelligence\QrArtworkAnalysisBridgeService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Qr360ArtworkAnalysisUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('public');
        config([
            'leads.artwork.disk' => 'public',
            'printing_intelligence.artwork_analysis_enabled' => true,
        ]);
    }

    public function test_run_analysis_button_appears_for_authorized_user(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork('Sales');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.public-quote-requests.show', $quoteRequest))
            ->assertOk()
            ->assertSee(__('Run Printing Intelligence Analysis'));
    }

    public function test_button_hidden_for_unauthorized_user(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork('Storekeeper', [
            'public_leads.quote_requests.view',
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.public-quote-requests.show', $quoteRequest))
            ->assertOk()
            ->assertDontSee(__('Run Printing Intelligence Analysis'));
    }

    public function test_result_panel_shows_analysis_summary(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork('Sales');
        $this->actingAs($user);

        app(QrArtworkAnalysisBridgeService::class)->run($quoteRequest, 'primary', [
            'steps' => ['metadata'],
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.public-quote-requests.show', $quoteRequest))
            ->assertOk()
            ->assertSee(__('Printing Intelligence'))
            ->assertSee(__('Printing Intelligence Results'))
            ->assertSee(__('View Analysis'))
            ->assertSee(__('Open in Printing Intelligence'))
            ->assertSee(__('Full results open in the analysis modal on this page.'))
            ->assertSee(__('Overview'))
            ->assertSee(__('Colour analysis'))
            ->assertSee(__('Ink estimate'))
            ->assertSee(__('Production'))
            ->assertSee(__('File info'))
            ->assertSee(__('Ink coverage summary'))
            ->assertSee(__('Ink coverage report'))
            ->assertSee(__('Ink Channel'))
            ->assertSee(__('Estimated Coverage'))
            ->assertSee(__('Cyan (C)'))
            ->assertSee(__('Quotation recommendation'));
    }

    public function test_run_analysis_returns_json_summary_for_modal(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork('Sales');

        $response = $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->postJson(route('admin.public-quote-requests.printing-analysis.run', [$quoteRequest, 'primary']));

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'warnings',
                'summary' => [
                    'analysis_status',
                    'show_url',
                    'file_info',
                    'dominant_colours',
                    'colour_analysis_warnings',
                    'ink_coverage',
                    'ink_estimate',
                    'production_estimate',
                    'quotation_estimate',
                ],
            ]);
    }

    public function test_modal_endpoint_returns_existing_summary(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork('Sales');
        $this->actingAs($user);

        app(QrArtworkAnalysisBridgeService::class)->run($quoteRequest, 'primary', [
            'steps' => ['metadata'],
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->getJson(route('admin.public-quote-requests.printing-analysis.modal', [$quoteRequest, 'primary']))
            ->assertOk()
            ->assertJsonPath('has_analysis', true)
            ->assertJsonStructure(['summary' => ['analysis_status_label', 'show_url']]);
    }

    public function test_open_in_printing_intelligence_link_works(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork('Sales');
        $this->actingAs($user);

        $result = app(QrArtworkAnalysisBridgeService::class)->run($quoteRequest, 'primary', [
            'steps' => ['metadata'],
            'uploaded_by' => $user->id,
        ]);

        $analysis = $result['analysis'];

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.artwork-analysis.show', $analysis))
            ->assertOk()
            ->assertSee($analysis->original_filename);
    }

    /**
     * @param  list<string>|null  $permissions
     * @return array{0: Company, 1: Branch, 2: User, 3: PublicQuoteRequest}
     */
    protected function quoteRequestWithArtwork(string $roleName, ?array $permissions = null): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        if ($permissions !== null) {
            Role::findByName('Storekeeper', 'web')->syncPermissions($permissions);
            $user->assignRole('Storekeeper');
        } else {
            $user->assignRole(Role::findByName($roleName, 'web'));
        }

        $file = UploadedFile::fake()->image('storefront-art.png', 300, 200);
        $path = $file->store('quote-artwork/2026/06', 'public');

        $quoteRequest = PublicQuoteRequest::query()->create([
            'name' => 'Storefront Client',
            'phone' => '+254700000088',
            'email' => 'storefront@example.com',
            'service_needed' => 'Posters',
            'message' => 'Poster artwork attached',
            'artwork_path' => $path,
            'artwork_original_name' => 'storefront-art.png',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        return [$company, $branch, $user, $quoteRequest];
    }
}
