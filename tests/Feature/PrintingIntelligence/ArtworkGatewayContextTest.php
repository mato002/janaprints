<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\PrintingIntelligenceGateway;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkGatewayContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_gateway_returns_artwork_context(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'gateway-test.png',
            'stored_filename' => 'gateway-test.png',
            'file_path' => 'printing-intelligence/artwork/'.$company->id.'/2026/06/gateway-test.png',
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'gateway-test'),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'page_count' => 1,
            'width_mm' => 210,
            'height_mm' => 297,
            'metadata' => ['width_px' => 1000, 'height_px' => 1414],
            'warnings' => [],
            'analyzed_at' => now(),
        ]);

        $context = app(PrintingIntelligenceGateway::class)->artworkContext($analysis);

        $this->assertSame($analysis->id, $context['analysis_id']);
        $this->assertSame('gateway-test.png', $context['original_filename']);
        $this->assertSame(1, $context['page_count']);
        $this->assertArrayHasKey('material_context_placeholder', $context);
        $this->assertArrayHasKey('ink_context_placeholder', $context);
    }

    public function test_gateway_does_not_expose_unsafe_file_path_by_default(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $analysis = PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => 'secret.pdf',
            'stored_filename' => 'secret.pdf',
            'file_path' => 'printing-intelligence/artwork/'.$company->id.'/secret.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'file_size_bytes' => 2048,
            'file_hash' => hash('sha256', 'secret'),
            'analysis_status' => ArtworkAnalysisStatus::ManualReview,
            'analysis_source' => 'upload',
        ]);

        $context = app(PrintingIntelligenceGateway::class)->artworkContext($analysis);

        $this->assertTrue($context['storage']['path_redacted']);
        $this->assertArrayNotHasKey('file_path', $context['storage']);

        $authorized = app(PrintingIntelligenceGateway::class)->artworkContext($analysis, true);
        $this->assertSame('printing-intelligence/artwork/'.$company->id.'/secret.pdf', $authorized['storage']['file_path']);
    }
}
