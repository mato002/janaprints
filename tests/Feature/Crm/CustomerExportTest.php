<?php

namespace Tests\Feature\Crm;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_customer_export_supports_csv_excel_and_pdf(): void
    {
        [$company, $branch, $user] = $this->tenantContext();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        Customer::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'company_name' => 'Export Test Co',
        ]);

        foreach (['csv', 'excel', 'pdf'] as $format) {
            $response = $this->actingAs($user)
                ->get(route('admin.crm.customers.export', ['format' => $format]))
                ->assertOk()
                ->assertHeader('content-disposition');

            if ($format === 'pdf') {
                $response->assertHeader('content-type', 'application/pdf');
                ob_start();
                $response->sendContent();
                $binary = ob_get_clean();
                $this->assertStringStartsWith('%PDF', $binary);
            }
        }
    }

    public function test_customers_index_renders_server_export_links(): void
    {
        [$company, $branch, $user] = $this->tenantContext();
        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.crm.customers.index', ['embedded' => '1']))
            ->assertOk()
            ->assertSee('customers/export/csv', false)
            ->assertSee('customers/export/excel', false)
            ->assertSee('customers/export/pdf', false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function tenantContext(): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Company Admin', 'web')->syncPermissions(['crm.customers.view']);
        $user->assignRole('Company Admin');

        return [$company, $branch, $user];
    }
}
