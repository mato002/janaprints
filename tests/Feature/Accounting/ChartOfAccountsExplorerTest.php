<?php

namespace Tests\Feature\Accounting;

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

class ChartOfAccountsExplorerTest extends TestCase
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

    public function test_explorer_index_renders_workspace(): void
    {
        [$user, $company, $branch] = $this->actingAsAccountant();

        $this->actingAs($user)
            ->get(route('admin.accounting.accounts.index'))
            ->assertOk()
            ->assertSee(__('Chart of Accounts'))
            ->assertSee(__('Account types'))
            ->assertSee(__('Account groups'))
            ->assertDontSee('Raw Materials');
    }

    public function test_explorer_groups_and_accounts_load_for_asset_bank_group(): void
    {
        [$user, $company] = $this->actingAsAccountant();

        $assetType = GlAccountType::query()->where('code', GlAccountTypeCode::Asset)->firstOrFail();
        $bankGroup = GlAccountGroup::query()
            ->where('company_id', $company->id)
            ->where('code', '1200')
            ->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('admin.accounting.accounts.explorer.groups', ['type_id' => $assetType->id]))
            ->assertOk()
            ->assertJsonFragment(['code' => '1200', 'name' => 'Bank']);

        $this->actingAs($user)
            ->getJson(route('admin.accounting.accounts.explorer.accounts', ['group_id' => $bankGroup->id]))
            ->assertOk()
            ->assertJsonFragment(['code' => '1210', 'name' => 'Equity Bank'])
            ->assertJsonFragment(['code' => '1220', 'name' => 'KCB Bank']);
    }

    public function test_explorer_search_finds_account_by_name(): void
    {
        [$user] = $this->actingAsAccountant();

        $this->actingAs($user)
            ->getJson(route('admin.accounting.accounts.explorer.search', ['q' => 'Banner']))
            ->assertOk()
            ->assertJsonFragment(['code' => '4110', 'name' => 'Banner Printing']);
    }

    public function test_explorer_panel_returns_account_metadata(): void
    {
        [$user, $company] = $this->actingAsAccountant();

        $account = GlAccount::query()
            ->where('company_id', $company->id)
            ->where('code', '4110')
            ->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('admin.accounting.accounts.explorer.panel', $account))
            ->assertOk()
            ->assertJsonPath('panel.code', '4110')
            ->assertJsonPath('panel.name', 'Banner Printing');
    }

    public function test_deactivate_account_via_explorer_endpoint(): void
    {
        [$user, $company, $branch] = $this->actingAsAccountant('Company Admin');

        $assetType = GlAccountType::query()->where('code', GlAccountTypeCode::Asset)->firstOrFail();
        $account = GlAccount::query()->create([
            'company_id' => $company->id,
            'gl_account_type_id' => $assetType->id,
            'code' => '1998',
            'name' => 'Explorer Deactivate Test',
            'normal_balance' => 'debit',
            'is_system' => false,
            'is_postable' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.accounting.accounts.deactivate', $account))
            ->assertOk()
            ->assertJsonPath('status', 'inactive');

        $this->assertSame('inactive', $account->fresh()->status->value);
    }

    /**
     * @return array{0: User, 1: Company, 2: Branch}
     */
    protected function actingAsAccountant(string $role = 'Accountant'): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        return [$user, $company, $branch];
    }
}
