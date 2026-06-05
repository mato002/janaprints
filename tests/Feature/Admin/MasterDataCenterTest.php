<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Crm\Customer;
use App\Models\MasterDataValue;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MasterDataCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_configuration_workspace_links_to_master_data(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->get(route('admin.workspaces.administration.section', ['section' => 'configuration']))
            ->assertOk()
            ->assertSee(route('admin.master-data.index'), false);
    }

    public function test_create_value(): void
    {
        $admin = $this->companyAdmin();

        $this->actingAs($admin)
            ->post(route('admin.master-data.store'), [
                'category_key' => 'payment_terms',
                'code' => 'net_45',
                'name' => 'Net 45',
                'description' => '45-day payment terms',
                'sort_order' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('master_data_values', [
            'company_id' => $admin->company_id,
            'category_key' => 'payment_terms',
            'code' => 'net_45',
            'name' => 'Net 45',
        ]);
    }

    public function test_edit_value(): void
    {
        $admin = $this->companyAdmin();
        $value = $this->createValue($admin->company_id, 'payment_terms', 'net_60', 'Net 60');

        $this->actingAs($admin)
            ->put(route('admin.master-data.update', $value), [
                'name' => 'Net 60 Updated',
                'description' => 'Updated terms',
                'sort_order' => 5,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('master_data_values', [
            'id' => $value->id,
            'name' => 'Net 60 Updated',
        ]);
    }

    public function test_deactivate_value(): void
    {
        $admin = $this->companyAdmin();
        $value = $this->createValue($admin->company_id, 'industry_types', 'retail', 'Retail');

        $this->actingAs($admin)
            ->patch(route('admin.master-data.deactivate', $value))
            ->assertRedirect();

        $this->assertFalse($value->fresh()->is_active);
    }

    public function test_dependency_check_blocks_deactivation_when_in_use(): void
    {
        $admin = $this->companyAdmin();
        $value = $this->createValue($admin->company_id, 'payment_terms', 'net_30', 'Net 30');

        Customer::factory()->create([
            'company_id' => $admin->company_id,
            'branch_id' => $admin->default_branch_id,
            'payment_terms' => 'Net 30',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.master-data.deactivate', $value))
            ->assertSessionHasErrors('is_active');

        $this->assertTrue($value->fresh()->is_active);
    }

    public function test_import_csv_rows(): void
    {
        $admin = $this->companyAdmin();

        $csv = "category_key,code,name,description,sort_order,is_active\n";
        $csv .= "job_types,offset,Offset Printing,Production jobs,1,1\n";

        $file = UploadedFile::fake()->createWithContent('master-data.csv', $csv);

        $this->actingAs($admin)
            ->post(route('admin.master-data.import'), ['file' => $file])
            ->assertRedirect();

        $this->assertDatabaseHas('master_data_values', [
            'company_id' => $admin->company_id,
            'category_key' => 'job_types',
            'code' => 'offset',
            'name' => 'Offset Printing',
        ]);
    }

    public function test_export_is_available(): void
    {
        $admin = $this->companyAdmin();
        $this->createValue($admin->company_id, 'payment_terms', 'net_10', 'Net 10');

        $this->actingAs($admin)
            ->get(route('admin.master-data.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_permission_enforcement_blocks_create_for_viewer(): void
    {
        $viewer = $this->viewer();

        $this->actingAs($viewer)
            ->post(route('admin.master-data.store'), [
                'category_key' => 'payment_terms',
                'code' => 'blocked',
                'name' => 'Blocked',
                'is_active' => 1,
            ])
            ->assertForbidden();
    }

    protected function companyAdmin()
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $company->branches()->first()->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function viewer()
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $user = \App\Models\User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $company->branches()->first()->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Viewer');

        return $user;
    }

    protected function createValue(int $companyId, string $category, string $code, string $name): MasterDataValue
    {
        return MasterDataValue::query()->create([
            'company_id' => $companyId,
            'category_key' => $category,
            'code' => $code,
            'name' => $name,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }
}
