<?php

namespace Tests\Feature\Commercial;

use App\Enums\CommercialReportExportStatus;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CommercialReportExport;
use App\Models\User;
use App\Support\Commercial\Reports\Exports\CommercialReportExportWriter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialReportExportFrameworkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_export_history_requires_view_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.sales.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.exports.index'))
            ->assertForbidden();
    }

    public function test_export_history_lists_company_exports(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.exports.view',
            'commercial.reports.exports.download',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        CommercialReportExport::query()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'module' => 'sales',
            'tab' => 'summary',
            'format' => 'csv',
            'scope_payload' => ['company_id' => $company->id],
            'status' => CommercialReportExportStatus::Queued,
            'queued_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.exports.index'))
            ->assertOk()
            ->assertSee(__('Export History'));
    }

    public function test_sales_export_queues_background_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.sales.view',
            'commercial.reports.sales.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.reports.sales.export', ['tab' => 'summary']), ['format' => 'csv'])
            ->assertRedirect()
            ->assertSessionHas('export_id');

        Queue::assertPushed(ProcessCommercialReportExportJob::class);

        $export = CommercialReportExport::query()->findOrFail(session('export_id'));
        $this->assertSame(CommercialReportExportStatus::Queued, $export->status);
        $this->assertNotNull($export->scope_payload);
    }

    public function test_process_job_writes_downloadable_file(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.sales.view',
            'commercial.reports.sales.export',
            'commercial.reports.exports.view',
            'commercial.reports.exports.download',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $export = CommercialReportExport::query()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'module' => 'sales',
            'tab' => 'summary',
            'format' => 'csv',
            'scope_payload' => [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'from_date' => now()->subMonth()->toDateString(),
                'to_date' => now()->toDateString(),
            ],
            'status' => CommercialReportExportStatus::Queued,
            'queued_at' => now(),
        ]);

        (new ProcessCommercialReportExportJob($export->id))->handle(app(CommercialReportExportWriter::class));

        $export->refresh();
        $this->assertTrue($export->isDownloadable());
        Storage::disk('local')->assertExists($export->storage_path);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.exports.download', $export))
            ->assertOk();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
