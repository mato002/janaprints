<?php

namespace Tests\Feature\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\CommercialReportExport;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialArtworkReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_artwork_reports_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['artwork.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.artwork.index'))
            ->assertForbidden();
    }

    public function test_artwork_reports_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.artwork.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.artwork.index'))
            ->assertOk()
            ->assertSee(__('Artwork Reports'), false)
            ->assertSee(__('Artwork Dashboard'), false)
            ->assertSee(__('Artwork Requests'), false);
    }

    public function test_artwork_reports_show_kpis_for_requests(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.artwork.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        ArtworkRequest::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'requested_by' => $user->id,
            'status' => ArtworkRequestStatus::Approved,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.artwork.index'))
            ->assertOk()
            ->assertSee(__('Total Artwork Requests'), false)
            ->assertSee(__('Approved Artwork'), false);
    }

    public function test_filters_persist_in_query_string(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.artwork.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.artwork.index', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-31',
                'tab' => 'pending',
                'search' => 'AR-100',
            ]))
            ->assertOk()
            ->assertSee('AR-100', false)
            ->assertSee(__('Artwork Pending'), false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.artwork.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.reports.artwork.export', ['format' => 'csv']))
            ->assertForbidden();
    }

    public function test_export_queues_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.artwork.view',
            'commercial.reports.artwork.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.reports.artwork.export', ['format' => 'csv', 'tab' => 'requests']))
            ->assertRedirect()
            ->assertSessionHas('export_id');

        Queue::assertPushed(ProcessCommercialReportExportJob::class);
        $this->assertNotNull(CommercialReportExport::query()->find(session('export_id')));
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create(['company_id' => $company->id]);

        $role = Role::create(['name' => 'test-role-'.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);
        $user->assignRole($role);

        return [$company, $branch, $user];
    }
}
