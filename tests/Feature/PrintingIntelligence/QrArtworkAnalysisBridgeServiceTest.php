<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisSource;
use App\Enums\ArtworkAnalysisStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PublicQuoteRequest;
use App\Models\User;
use App\Services\PrintingIntelligence\QrArtworkAnalysisBridgeService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QrArtworkAnalysisBridgeServiceTest extends TestCase
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
            'printing_intelligence.colour_analysis_enabled' => true,
            'printing_intelligence.ink_costing_enabled' => true,
            'printing_intelligence.production_costing_enabled' => true,
        ]);
    }

    public function test_creates_analysis_from_qr_artwork_file(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork();
        $this->actingAs($user);

        $result = app(QrArtworkAnalysisBridgeService::class)->run($quoteRequest, 'primary', [
            'steps' => ['metadata'],
            'uploaded_by' => $user->id,
        ]);

        $analysis = $result['analysis'];

        $this->assertDatabaseHas('print_artwork_analyses', [
            'id' => $analysis->id,
            'company_id' => $company->id,
            'public_quote_request_id' => $quoteRequest->id,
            'source_file_model' => PublicQuoteRequest::class,
            'source_file_id' => $quoteRequest->id,
            'analysis_source' => ArtworkAnalysisSource::QuoteRequest->value,
            'disk' => 'public',
            'file_path' => $quoteRequest->artwork_path,
        ]);

        $this->assertContains($analysis->analysis_status, [
            ArtworkAnalysisStatus::Completed,
            ArtworkAnalysisStatus::ManualReview,
        ]);

        Storage::disk('public')->assertExists($quoteRequest->artwork_path);
        $this->assertDatabaseCount('print_artwork_analyses', 1);
    }

    public function test_does_not_duplicate_analysis_on_rerun(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork();
        $this->actingAs($user);
        $service = app(QrArtworkAnalysisBridgeService::class);

        $first = $service->run($quoteRequest, 'primary', ['steps' => ['metadata'], 'uploaded_by' => $user->id]);
        $second = $service->run($quoteRequest, 'primary', [
            'steps' => ['metadata'],
            'force_rerun' => true,
            'uploaded_by' => $user->id,
        ]);

        $this->assertSame($first['analysis']->id, $second['analysis']->id);
        $this->assertDatabaseCount('print_artwork_analyses', 1);
    }

    public function test_enforces_same_company(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork();
        $otherCompany = Company::factory()->create();

        $quoteRequest->update(['company_id' => $otherCompany->id]);

        $this->actingAs($user);

        $this->expectException(AuthorizationException::class);

        app(QrArtworkAnalysisBridgeService::class)->run($quoteRequest, 'primary', [
            'steps' => ['metadata'],
            'uploaded_by' => $user->id,
        ]);
    }

    public function test_skips_ink_estimate_when_no_profile_exists(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork();
        $this->actingAs($user);

        $result = app(QrArtworkAnalysisBridgeService::class)->run($quoteRequest, 'primary', [
            'steps' => ['metadata', 'colour', 'ink'],
            'force_rerun' => true,
            'uploaded_by' => $user->id,
        ]);

        $this->assertContains(
            __('No active ink profile available; ink estimate skipped.'),
            $result['warnings'],
        );
        $this->assertDatabaseCount('print_artwork_ink_estimates', 0);
    }

    public function test_skips_machine_estimate_when_no_machine_exists(): void
    {
        [$company, $branch, $user, $quoteRequest] = $this->quoteRequestWithArtwork();
        $this->actingAs($user);

        $result = app(QrArtworkAnalysisBridgeService::class)->run($quoteRequest, 'primary', [
            'steps' => ['metadata', 'colour', 'production'],
            'force_rerun' => true,
            'uploaded_by' => $user->id,
        ]);

        $this->assertContains(
            __('No production machine profile available; machine estimate skipped.'),
            $result['warnings'],
        );
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: PublicQuoteRequest}
     */
    protected function quoteRequestWithArtwork(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('Production', 'web'));

        $file = UploadedFile::fake()->image('qr-client.png', 320, 240);
        $path = $file->store('quote-artwork/2026/06', 'public');

        $quoteRequest = PublicQuoteRequest::query()->create([
            'name' => 'Client User',
            'phone' => '+254700000099',
            'email' => 'client@example.com',
            'service_needed' => 'Flyers',
            'message' => 'Need flyers',
            'artwork_path' => $path,
            'artwork_original_name' => 'qr-client.png',
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        return [$company, $branch, $user, $quoteRequest];
    }
}
