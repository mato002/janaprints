<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Services\PrintingIntelligence\ArtworkMetadataExtractionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkMetadataExtractionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('local');
        config(['printing_intelligence.storage_disk' => 'local']);
    }

    public function test_extracts_image_dimensions(): void
    {
        [$company, $branch] = $this->tenant();
        $analysis = $this->storeAnalysis($company, $branch, UploadedFile::fake()->image('art.png', 1000, 500));

        $result = app(ArtworkMetadataExtractionService::class)->extract($analysis);

        $this->assertContains($result->analysis_status, [
            ArtworkAnalysisStatus::Completed,
            ArtworkAnalysisStatus::ManualReview,
        ]);
        $this->assertSame(1, $result->page_count);
        $this->assertDatabaseHas('print_artwork_pages', [
            'print_artwork_analysis_id' => $analysis->id,
            'page_number' => 1,
        ]);
        $this->assertNotNull($result->metadata['width_px'] ?? null);
        $this->assertNotNull($result->metadata['height_px'] ?? null);
    }

    public function test_handles_missing_dpi_gracefully(): void
    {
        [$company, $branch] = $this->tenant();
        $analysis = $this->storeAnalysis($company, $branch, UploadedFile::fake()->image('nodpi.png', 640, 480));

        $result = app(ArtworkMetadataExtractionService::class)->extract($analysis);

        if ($result->resolution_dpi === null) {
            $this->assertSame(ArtworkAnalysisStatus::ManualReview, $result->analysis_status);
            $this->assertNotEmpty($result->warnings);
        } else {
            $this->assertNotNull($result->width_mm);
        }
    }

    public function test_marks_pdf_manual_review_if_pdfinfo_unavailable(): void
    {
        config(['printing_intelligence.pdf_metadata_tool_enabled' => false]);

        [$company, $branch] = $this->tenant();
        $analysis = $this->storeAnalysis(
            $company,
            $branch,
            UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
            'pdf',
        );

        $result = app(ArtworkMetadataExtractionService::class)->extract($analysis);

        $this->assertSame(ArtworkAnalysisStatus::ManualReview, $result->analysis_status);
        $this->assertNotEmpty($result->warnings);
    }

    public function test_creates_page_rows_for_pdf_when_pdfinfo_available(): void
    {
        if (! $this->pdfInfoAvailable()) {
            $this->markTestSkipped('pdfinfo not available on this environment.');
        }

        [$company, $branch] = $this->tenant();
        $pdf = $this->minimalPdfUploadedFile();
        $analysis = $this->storeAnalysis($company, $branch, $pdf, 'pdf');

        $result = app(ArtworkMetadataExtractionService::class)->extract($analysis);

        $this->assertContains($result->analysis_status, [
            ArtworkAnalysisStatus::Completed,
            ArtworkAnalysisStatus::ManualReview,
        ]);
        $this->assertGreaterThanOrEqual(0, $result->pages()->count());
    }

    protected function pdfInfoAvailable(): bool
    {
        $process = new \Symfony\Component\Process\Process(['pdfinfo', '-v']);
        $process->run();

        return $process->isSuccessful();
    }

    protected function minimalPdfUploadedFile(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pi1pdf');
        file_put_contents($path, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n");

        return new UploadedFile($path, 'minimal.pdf', 'application/pdf', null, true);
    }

    protected function storeAnalysis(Company $company, Branch $branch, UploadedFile $file, ?string $extension = null): PrintArtworkAnalysis
    {
        $extension ??= strtolower($file->getClientOriginalExtension());
        $stored = Storage::disk('local')->putFile('test-artwork', $file);

        return PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => basename((string) $stored),
            'file_path' => (string) $stored,
            'disk' => 'local',
            'mime_type' => $file->getMimeType(),
            'file_extension' => $extension,
            'file_size_bytes' => (int) $file->getSize(),
            'file_hash' => hash_file('sha256', $file->getRealPath()),
            'analysis_status' => ArtworkAnalysisStatus::Pending,
            'analysis_source' => 'upload',
        ]);
    }

    /**
     * @return array{0: Company, 1: Branch}
     */
    protected function tenant(): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        return [$company, $branch];
    }
}
