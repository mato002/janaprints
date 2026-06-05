<?php

namespace Tests\Feature\Commercial;

use App\Enums\QuotationStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Sales\Quotation;
use App\Jobs\Commercial\ProcessCommercialReportExportJob;
use App\Models\CommercialReportExport;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialQuotationReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_quotation_reports_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('commercial.reports.quotations.index'))
            ->assertForbidden();
    }

    public function test_quotation_reports_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.quotations.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('commercial.reports.quotations.index'))
            ->assertOk()
            ->assertSee(__('Quotation Reports'), false)
            ->assertSee(__('Data Readiness'), false)
            ->assertSee(__('Quotation Dashboard'), false)
            ->assertSee(__('Quotation Summary'), false);
    }

    public function test_quotation_reports_show_kpis_for_issued_quotes(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.quotations.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'total_amount' => 50000,
            'prepared_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('commercial.reports.quotations.index'))
            ->assertOk()
            ->assertSee(__('Quotes Issued'), false)
            ->assertSee('50,000', false);
    }

    public function test_filters_persist_in_query_string(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.quotations.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('commercial.reports.quotations.index', [
                'from_date' => '2026-02-01',
                'to_date' => '2026-02-28',
                'expiry_status' => 'valid',
                'tab' => 'open',
                'search' => 'QUO-100',
            ]))
            ->assertOk()
            ->assertSee('value="2026-02-01"', false)
            ->assertSee('QUO-100', false)
            ->assertSee(__('Open Quotations'), false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.quotations.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('commercial.reports.quotations.export', ['tab' => 'summary']), ['format' => 'csv'])
            ->assertForbidden();
    }

    public function test_export_queues_job(): void
    {
        Queue::fake();

        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.quotations.view',
            'commercial.reports.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('commercial.reports.quotations.export', ['tab' => 'summary']), ['format' => 'csv'])
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
