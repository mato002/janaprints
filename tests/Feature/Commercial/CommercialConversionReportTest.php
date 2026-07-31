<?php

namespace Tests\Feature\Commercial;

use App\Enums\LeadStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Lead;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialConversionReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_conversion_reports_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.conversion.index'))
            ->assertForbidden();
    }

    public function test_conversion_reports_index_loads(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.conversion.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.conversion.index'))
            ->assertOk()
            ->assertSee(__('Conversion Reports'), false)
            ->assertSee(__('Conversion Dashboard'), false)
            ->assertSee(__('Full Commercial Funnel'), false);
    }

    public function test_conversion_reports_show_funnel_counts(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.conversion.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        Lead::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'lead_name' => 'Acme Prospect',
            'status' => LeadStatus::Open,
            'assigned_to' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Quotation::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => QuotationStatus::Sent,
            'quotation_date' => now()->toDateString(),
            'prepared_by' => $user->id,
        ]);

        SalesOrder::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => SalesOrderStatus::Confirmed,
            'order_date' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.conversion.index'))
            ->assertOk()
            ->assertSee(__('Leads'), false)
            ->assertSee(__('Quotes'), false)
            ->assertSee(__('Orders'), false);
    }

    public function test_filters_persist_in_query_string(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.conversion.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.commercial.reports.conversion.index', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-31',
                'branch_id' => $branch->id,
                'tab' => 'lead_to_quote',
                'search' => 'Acme',
            ]))
            ->assertOk()
            ->assertSee('value="2026-01-01"', false)
            ->assertSee('value="2026-01-31"', false)
            ->assertSee('Acme', false)
            ->assertSee(__('Lead → Quote'), false);
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['commercial.reports.conversion.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.commercial.reports.conversion.export', ['tab' => 'full_funnel']), ['format' => 'csv'])
            ->assertForbidden();
    }

    public function test_export_streams_file(): void
    {
        [$company, $branch, $user] = $this->tenantUser([
            'commercial.reports.conversion.view',
            'commercial.reports.conversion.export',
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->post(route('admin.commercial.reports.conversion.export', ['tab' => 'full_funnel']), ['format' => 'csv']);

        $response->assertOk();
        $response->assertHeader('X-Erp-Export', 'direct');
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
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
