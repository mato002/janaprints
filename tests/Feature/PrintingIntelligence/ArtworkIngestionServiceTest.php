<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\PrintingIntelligence\ArtworkIngestionService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ArtworkIngestionServiceTest extends TestCase
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

    public function test_uploads_supported_image(): void
    {
        [$company, $branch] = $this->tenant();

        $file = UploadedFile::fake()->image('banner.png', 800, 600);

        $analysis = app(ArtworkIngestionService::class)->ingest($file, [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $this->assertSame('banner.png', $analysis->original_filename);
        $this->assertSame('png', $analysis->file_extension);
        $this->assertSame(ArtworkAnalysisStatus::Pending, $analysis->analysis_status);
        Storage::disk('local')->assertExists($analysis->file_path);
    }

    public function test_rejects_unsupported_file(): void
    {
        [$company, $branch] = $this->tenant();

        $file = UploadedFile::fake()->create('design.psd', 100, 'application/octet-stream');

        $this->expectException(ValidationException::class);

        app(ArtworkIngestionService::class)->ingest($file, [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_stores_file_hash(): void
    {
        [$company, $branch] = $this->tenant();

        $file = UploadedFile::fake()->image('sample.jpg', 200, 200);
        $expectedHash = hash_file('sha256', $file->getRealPath());

        $analysis = app(ArtworkIngestionService::class)->ingest($file, [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        $this->assertSame($expectedHash, $analysis->file_hash);
        $this->assertGreaterThan(0, $analysis->file_size_bytes);
    }

    public function test_prevents_duplicate_hash_within_company(): void
    {
        [$company, $branch] = $this->tenant();
        $service = app(ArtworkIngestionService::class);

        $file = UploadedFile::fake()->image('dup.png', 100, 100);
        $first = $service->ingest($file, ['company_id' => $company->id, 'branch_id' => $branch->id]);

        $duplicate = new UploadedFile(
            $file->getRealPath(),
            'dup-copy.png',
            'image/png',
            null,
            true,
        );

        $second = $service->ingest($duplicate, ['company_id' => $company->id, 'branch_id' => $branch->id]);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('print_artwork_analyses', 1);
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
