<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Services\PrintingIntelligence\ArtworkAnalysisService;
use App\Services\PrintingIntelligence\ArtworkMetadataExtractionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ArtworkAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('local');
        config(['printing_intelligence.storage_disk' => 'local', 'printing_intelligence.artwork_analysis_enabled' => true]);
    }

    public function test_transitions_pending_to_processing_to_completed(): void
    {
        [$company, $branch, $user] = $this->tenantUser();
        $this->actingAs($user);

        $analysis = app(ArtworkAnalysisService::class)->analyzeUploadedFile(
            UploadedFile::fake()->image('flow.jpg', 400, 300),
            ['company_id' => $company->id, 'branch_id' => $branch->id],
        );

        $this->assertContains($analysis->analysis_status, [
            ArtworkAnalysisStatus::Completed,
            ArtworkAnalysisStatus::ManualReview,
        ]);
        $this->assertNotNull($analysis->analyzed_at);
    }

    public function test_records_warnings(): void
    {
        [$company, $branch, $user] = $this->tenantUser();
        $this->actingAs($user);

        config(['printing_intelligence.pdf_metadata_tool_enabled' => false]);

        $analysis = app(ArtworkAnalysisService::class)->analyzeUploadedFile(
            UploadedFile::fake()->create('warn.pdf', 20, 'application/pdf'),
            ['company_id' => $company->id, 'branch_id' => $branch->id],
        );

        $this->assertSame(ArtworkAnalysisStatus::ManualReview, $analysis->analysis_status);
        $this->assertNotEmpty($analysis->warnings);
    }

    public function test_records_failures_safely(): void
    {
        [$company, $branch, $user] = $this->tenantUser();
        $this->actingAs($user);

        $mock = Mockery::mock(ArtworkMetadataExtractionService::class);
        $mock->shouldReceive('extract')->andThrow(new \RuntimeException('Simulated extractor failure'));
        $this->app->instance(ArtworkMetadataExtractionService::class, $mock);

        $analysis = app(ArtworkAnalysisService::class)->analyzeUploadedFile(
            UploadedFile::fake()->image('fail.png', 100, 100),
            ['company_id' => $company->id, 'branch_id' => $branch->id],
        );

        $this->assertSame(ArtworkAnalysisStatus::Failed, $analysis->analysis_status);
        $this->assertNotNull($analysis->failed_at);
        $this->assertNotEmpty($analysis->errors);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: \App\Models\User}
     */
    protected function tenantUser(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return [$company, $branch, $user];
    }
}
