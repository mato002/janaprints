<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyAdminSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_company_admin_role_includes_companies_manage_permission(): void
    {
        $role = Role::findByName('Company Admin', 'web');

        $this->assertTrue($role->hasPermissionTo('companies.manage'));
    }

    public function test_company_admin_can_view_companies_index_scoped_to_own_company(): void
    {
        $ownCompany = Company::query()->where('code', 'JANA')->firstOrFail();
        $otherCompany = Company::factory()->create(['code' => 'OTHER']);
        $admin = $this->companyAdmin($ownCompany);

        $this->actingAs($admin)
            ->get(route('admin.companies.index'))
            ->assertOk()
            ->assertSee($ownCompany->name)
            ->assertDontSee($otherCompany->name);
    }

    public function test_company_admin_can_edit_own_company_profile(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $admin = $this->companyAdmin($company);

        $this->actingAs($admin)
            ->get(route('admin.companies.edit', $company))
            ->assertOk()
            ->assertSee(__('Manage tax settings'), false);

        $this->actingAs($admin)
            ->put(route('admin.companies.update', $company), [
                'name' => 'Jana Prints Updated',
                'email' => 'admin@janaprints.local',
                'phone' => '+254700000000',
                'address' => 'Nairobi, Kenya',
            ])
            ->assertRedirect(route('admin.companies.index'));

        $company->refresh();

        $this->assertSame('Jana Prints Updated', $company->name);
        $this->assertSame('admin@janaprints.local', $company->email);
        $this->assertSame('+254700000000', $company->phone);
        $this->assertSame('Nairobi, Kenya', $company->address);
    }

    public function test_company_admin_cannot_tamper_with_protected_fields_on_own_company(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $admin = $this->companyAdmin($company);

        $this->actingAs($admin)
            ->put(route('admin.companies.update', $company), [
                'name' => 'Jana Prints',
                'code' => 'HACKED',
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.companies.index'));

        $company->refresh();

        $this->assertSame('JANA', $company->code);
        $this->assertTrue($company->is_active);
    }

    public function test_company_admin_cannot_edit_other_company(): void
    {
        $ownCompany = Company::query()->where('code', 'JANA')->firstOrFail();
        $otherCompany = Company::factory()->create(['code' => 'RIVAL']);
        $admin = $this->companyAdmin($ownCompany);

        $this->actingAs($admin)
            ->get(route('admin.companies.edit', $otherCompany))
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.companies.update', $otherCompany), [
                'name' => 'Compromised Name',
            ])
            ->assertForbidden();

        $this->assertNotSame('Compromised Name', $otherCompany->fresh()->name);
    }

    public function test_company_admin_cannot_create_company(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $admin = $this->companyAdmin($company);

        $this->actingAs($admin)
            ->get(route('admin.companies.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.companies.store'), [
                'name' => 'New Entity',
                'code' => 'NEWCO',
                'is_active' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('companies', ['code' => 'NEWCO']);
    }

    public function test_company_admin_cannot_delete_company(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $admin = $this->companyAdmin($company);

        $this->actingAs($admin)
            ->delete(route('admin.companies.destroy', $company))
            ->assertForbidden();

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    protected function companyAdmin(?Company $company = null): User
    {
        $company ??= Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->first()
            ?? Branch::factory()->create(['company_id' => $company->id, 'code' => 'HQ', 'is_head_office' => true]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }
}
