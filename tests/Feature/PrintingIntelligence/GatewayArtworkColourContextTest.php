<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Enums\CoverageClass;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayArtworkColourContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.ink_costing_enabled' => true]);
    }

    public function test_gateway_returns_colour_context(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'colour-gateway.png',
            'stored_filename' => 'colour-gateway.png',
            'file_path' => 'printing-intelligence/artwork/test.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'colour-gateway'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Completed,
            'cmyk_coverage_percent' => 42.5,
            'coverage_class' => CoverageClass::Medium,
            'dominant_colours' => [['hex' => '#FF0000', 'percent' => 80]],
            'colour_analysis_warnings' => [],
        ]);

        $context = app(PrintingIntelligenceGateway::class)->artworkColourContext($analysis);

        $this->assertSame('medium', $context['coverage_class']);
        $this->assertEqualsWithDelta(42.5, $context['cmyk_coverage_percent'], 0.01);
        $this->assertArrayNotHasKey('file_path', $context);
        $this->assertTrue($context['ink_costing_ready']);
    }
}
