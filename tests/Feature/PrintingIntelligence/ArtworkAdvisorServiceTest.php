<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\Advisor\ArtworkAdvisorService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkAdvisorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_high_coverage_artwork_generates_recommendation(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'original_filename' => 'heavy.pdf',
            'stored_filename' => 'heavy.pdf',
            'file_path' => 'printing-intelligence/artwork/heavy.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'file_size_bytes' => 1000,
            'file_hash' => hash('sha256', 'heavy-advisor'),
            'analysis_status' => \App\Enums\ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'cmyk_coverage_percent' => 80,
            'black_coverage_percent' => 45,
        ]);

        $recs = app(ArtworkAdvisorService::class)->recommend(['company_id' => $company->id]);

        $this->assertNotEmpty($recs);
        $this->assertTrue(collect($recs)->contains(fn ($r) => str_starts_with($r['rule_code'], 'artwork:high_coverage:')));
    }
}
