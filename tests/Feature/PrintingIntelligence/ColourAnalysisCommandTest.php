<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ArtworkAnalysisStatus;
use App\Enums\ColourAnalysisStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ColourAnalysisCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        Storage::fake('local');
    }

    public function test_processes_pending_analyses(): void
    {
        [$company, $branch] = $this->tenant();
        $this->createPendingAnalysis($company, $branch);

        $this->artisan('printing:artwork:analyse-colour', ['--pending' => true, '--limit' => 5])
            ->assertSuccessful();

        $this->assertNotSame(
            ColourAnalysisStatus::Pending->value,
            PrintArtworkAnalysis::query()->value('colour_analysis_status'),
        );
    }

    public function test_dry_run_does_not_mutate(): void
    {
        [$company, $branch] = $this->tenant();
        $this->createPendingAnalysis($company, $branch);

        $this->artisan('printing:artwork:analyse-colour', ['--pending' => true, '--dry-run' => true])
            ->assertSuccessful();

        $this->assertSame(
            ColourAnalysisStatus::Pending,
            PrintArtworkAnalysis::query()->first()?->colour_analysis_status,
        );
    }

    public function test_respects_limit(): void
    {
        [$company, $branch] = $this->tenant();
        $this->createPendingAnalysis($company, $branch);
        $this->createPendingAnalysis($company, $branch);

        $this->artisan('printing:artwork:analyse-colour', ['--pending' => true, '--limit' => 1])
            ->assertSuccessful();

        $this->assertSame(1, PrintArtworkAnalysis::query()->where('colour_analysis_status', '!=', ColourAnalysisStatus::Pending->value)->count());
    }

    protected function createPendingAnalysis(Company $company, Branch $branch): PrintArtworkAnalysis
    {
        $file = UploadedFile::fake()->image('cmd-'.uniqid().'.png', 40, 40);
        $stored = Storage::disk('local')->putFile('artwork', $file);

        return PrintArtworkAnalysis::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'original_filename' => basename((string) $stored),
            'stored_filename' => basename((string) $stored),
            'file_path' => (string) $stored,
            'disk' => 'local',
            'mime_type' => 'image/png',
            'file_extension' => 'png',
            'file_size_bytes' => 500,
            'file_hash' => hash_file('sha256', $file->getRealPath()),
            'analysis_status' => ArtworkAnalysisStatus::Completed,
            'analysis_source' => 'upload',
            'colour_analysis_status' => ColourAnalysisStatus::Pending,
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
