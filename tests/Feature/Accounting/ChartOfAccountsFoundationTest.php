<?php

namespace Tests\Feature\Accounting;

use App\Enums\GlAccountStatus;
use App\Enums\GlAccountTypeCode;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\GlAccountGroup;
use App\Models\Accounting\GlAccountType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Support\Accounting\JanaPrintsChartOfAccountsSeedService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountsFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        app(JanaPrintsChartOfAccountsSeedService::class)->seedByCompanyCode('JANA');
    }

    public function test_account_types_are_seeded(): void
    {
        $this->assertDatabaseCount('gl_account_types', 6);
        $this->assertDatabaseHas('gl_account_types', [
            'code' => GlAccountTypeCode::CostOfSales->value,
            'normal_balance' => 'debit',
        ]);
    }

    public function test_jana_standard_chart_includes_printing_accounts(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        foreach ([
            '1410' => 'Raw Materials',
            '1420' => 'Work In Progress',
            '1430' => 'Finished Goods',
            '4110' => 'Banner Printing',
            '4200' => 'Design Revenue',
            '4300' => 'Delivery Revenue',
            '5100' => 'Paper Consumption',
            '5200' => 'Ink Consumption',
            '5400' => 'Outsourced Printing',
        ] as $code => $name) {
            $this->assertDatabaseHas('gl_accounts', [
                'company_id' => $company->id,
                'code' => $code,
                'name' => $name,
                'is_system' => true,
            ]);
        }
    }

    public function test_inventory_accounts_use_parent_child_hierarchy(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $header = GlAccount::query()->where('company_id', $company->id)->where('code', '1400')->firstOrFail();
        $raw = GlAccount::query()->where('company_id', $company->id)->where('code', '1410')->firstOrFail();

        $this->assertFalse($header->is_postable);
        $this->assertTrue($raw->is_postable);
        $this->assertSame($header->id, $raw->parent_id);
    }

    public function test_accountant_can_view_chart_index(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->userWithRole('Accountant', $company, $branch);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.accounting.accounts.index'))
            ->assertOk()
            ->assertSee(__('Chart of Accounts'))
            ->assertSee(__('Account types'));

        $assetsGroup = GlAccountGroup::query()
            ->where('company_id', $company->id)
            ->where('code', '1000')
            ->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('admin.accounting.accounts.explorer.accounts', [
                'group_id' => $assetsGroup->id,
            ]))
            ->assertOk()
            ->assertJsonFragment(['code' => '1410', 'name' => 'Raw Materials']);
    }

    public function test_company_isolation_for_accounts(): void
    {
        $companyA = Company::factory()->create(['code' => 'CA']);
        $companyB = Company::query()->where('code', 'JANA')->firstOrFail();
        $branchB = Branch::factory()->create(['company_id' => $companyB->id, 'code' => 'HB']);
        $branchA = Branch::factory()->create(['company_id' => $companyA->id, 'code' => 'HA']);

        app(JanaPrintsChartOfAccountsSeedService::class)->seedCompany($companyA);

        $accountB = GlAccount::query()->where('company_id', $companyB->id)->firstOrFail();
        $userA = $this->userWithRole('Accountant', $companyA, $branchA, ['accounting.chart.view']);

        session(['active_company_id' => $companyA->id, 'active_branch_id' => $branchA->id]);

        $this->actingAs($userA)
            ->get(route('admin.accounting.accounts.show', $accountB))
            ->assertForbidden();
    }

    public function test_create_account_form_has_branch_plus_button(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->userWithRole('Company Admin', $company, $branch);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.accounting.accounts.create'))
            ->assertOk()
            ->assertSee('erp-lookup-select__add', false)
            ->assertSee('erpLookupCreate', false);
    }

    public function test_user_can_create_child_account(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->userWithRole('Company Admin', $company, $branch);
        $assetType = GlAccountType::query()->where('code', GlAccountTypeCode::Asset)->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('admin.accounting.accounts.store'), [
            'gl_account_type_id' => $assetType->id,
            'code' => '1199',
            'name' => 'Test Petty Cash',
            'normal_balance' => 'debit',
            'is_postable' => '1',
            'sort_order' => 99,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gl_accounts', [
            'company_id' => $company->id,
            'code' => '1199',
            'name' => 'Test Petty Cash',
            'is_system' => false,
        ]);
    }

    public function test_locked_account_cannot_be_updated(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->userWithRole('Company Admin', $company, $branch);

        $account = GlAccount::query()->where('company_id', $company->id)->where('code', '4110')->firstOrFail();
        $account->update(['status' => GlAccountStatus::Locked]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->put(route('admin.accounting.accounts.update', $account), [
                'gl_account_type_id' => $account->gl_account_type_id,
                'code' => $account->code,
                'name' => 'Renamed Revenue',
                'normal_balance' => $account->normal_balance->value,
                'status' => GlAccountStatus::Active->value,
            ])
            ->assertForbidden();
    }

    public function test_lock_and_unlock_account(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->userWithRole('Company Admin', $company, $branch);

        $assetType = GlAccountType::query()->where('code', GlAccountTypeCode::Asset)->firstOrFail();
        $account = GlAccount::query()->create([
            'company_id' => $company->id,
            'gl_account_type_id' => $assetType->id,
            'code' => '1999',
            'name' => 'Temp Lock Test',
            'normal_balance' => 'debit',
            'status' => GlAccountStatus::Active,
            'is_system' => false,
            'is_postable' => true,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.accounting.accounts.lock', $account))
            ->assertRedirect();

        $this->assertSame(GlAccountStatus::Locked, $account->fresh()->status);

        $this->actingAs($user)
            ->post(route('admin.accounting.accounts.unlock', $account))
            ->assertRedirect();

        $this->assertSame(GlAccountStatus::Active, $account->fresh()->status);
    }

    public function test_system_account_cannot_be_deleted(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = $this->userWithRole('Company Admin', $company, $branch);

        $account = GlAccount::query()->where('company_id', $company->id)->where('code', '4110')->firstOrFail();

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->delete(route('admin.accounting.accounts.destroy', $account))
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     */
    protected function userWithRole(string $role, Company $company, Branch $branch, ?array $permissions = null): User
    {
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->assignRole($role);

        if ($permissions !== null) {
            $user->syncPermissions($permissions);
        }

        return $user;
    }
}
