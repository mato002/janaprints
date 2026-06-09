<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetAcquisitionAccountingStatus;
use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetType;
use App\Enums\PostingEventCode;
use App\Models\Assets\AssetCategory;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Assets\AssetManualAcquisitionPostingService;
use App\Services\Assets\AssetRegisterService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * ASSET-H5 coverage matrix — maps required accounting paths to executable tests.
 */
class AssetAccountingCoverageMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
        $this->seed(PlatformConfigurationSeeder::class);
        $this->seed(GlAccountTypeSeeder::class);
        $this->seed(JanaPrintsChartOfAccountsSeeder::class);
        $this->seed(JanaPrintsAccountingPeriodsSeeder::class);
        $this->seed(JanaPrintsPostingEngineSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function requiredAccountingPathCoverage(): array
    {
        return [
            'acquisition_journal' => ['AssetAccountingJournalTest', 'test_acquisition_journal_posts_balanced_gl_entries'],
            'capitalization_journal' => ['AssetAccountingJournalTest', 'test_capitalization_journal_posts_acquisition_on_capitalize'],
            'depreciation_journal' => ['AssetAccountingJournalTest', 'test_depreciation_journal_posts_expense_and_accumulated_depreciation'],
            'write_off_journal' => ['AssetAccountingJournalTest', 'test_write_off_journal_posts_loss_and_clears_asset_accounts'],
            'disposal_journal' => ['AssetAccountingJournalTest', 'test_disposal_journal_posts_full_retirement_entry'],
            'gain_scenario' => ['AssetAccountingJournalTest', 'test_disposal_gain_scenario_credits_gain_account'],
            'loss_scenario' => ['AssetAccountingJournalTest', 'test_disposal_loss_scenario_debits_loss_account'],
            'write_off_approval_gate' => ['AssetAccountingJournalTest', 'test_write_off_approval_gate_blocks_posting_until_approved'],
            'disposal_approval_gate' => ['AssetAccountingJournalTest', 'test_disposal_approval_gate_blocks_posting_until_approved'],
            'approved_write_off' => ['AssetAccountingJournalTest', 'test_approved_write_off_posts_journal_after_approval'],
            'approved_disposal' => ['AssetAccountingJournalTest', 'test_approved_disposal_posts_journal_after_approval'],
            'acquisition_reversal' => ['AssetAccountingJournalTest', 'test_acquisition_journal_reversal_restores_gl_balances'],
            'capitalization_reversal' => ['AssetAccountingJournalTest', 'test_capitalization_journal_reversal_restores_gl_balances'],
            'depreciation_reversal' => ['AssetAccountingJournalTest', 'test_depreciation_journal_reversal_restores_gl_balances'],
            'write_off_reversal' => ['AssetAccountingJournalTest', 'test_write_off_journal_reversal_restores_gl_balances'],
            'disposal_reversal' => ['AssetAccountingJournalTest', 'test_disposal_journal_reversal_restores_gl_balances'],
            'disposal_gain_reversal' => ['AssetAccountingJournalTest', 'test_disposal_gain_reversal_restores_gain_account_balance'],
            'manual_acquisition' => ['ManualAssetAcquisitionPostingTest', 'test_option_b_post_later_from_asset_profile'],
            'capitalization_recovery' => ['CapitalizationPostingRecoveryTest', 'test_recovery_posting_creates_acquisition_journal'],
        ];
    }

    #[DataProvider('requiredAccountingPathCoverage')]
    public function test_required_accounting_path_has_test_method(string $testClass, string $testMethod): void
    {
        $fqcn = __NAMESPACE__.'\\'.$testClass;

        $this->assertTrue(
            method_exists($fqcn, $testMethod),
            "Missing coverage for {$testClass}::{$testMethod}",
        );
    }

    public function test_manual_acquisition_journal_posts_balanced_gl_entries(): void
    {
        $user = $this->financeUser();
        $category = $this->makeCategory();

        $asset = app(AssetRegisterService::class)->create([
            'asset_category_id' => $category->id,
            'asset_name' => 'Manual Matrix Asset',
            'acquisition_date' => now()->toDateString(),
            'capitalization_date' => now()->toDateString(),
            'acquisition_cost' => 88000,
            'acquisition_source' => AssetAcquisitionSource::Manual,
        ], $this->company->id, $this->branch->id, $user->id);

        $journal = app(AssetManualAcquisitionPostingService::class)->post($asset, $user->id);

        $this->assertSame(PostingEventCode::AssetAcquisitionPosted->value, $journal->posting_event);
        $this->assertTrue($journal->isBalanced());
        $this->assertSame(AssetAcquisitionAccountingStatus::Posted, $asset->fresh()->acquisition_accounting_status);
    }

    protected function financeUser(): User
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
        $user->assignRole('Company Admin');

        return $user;
    }

    protected function makeCategory(): AssetCategory
    {
        return AssetCategory::query()->create([
            'company_id' => $this->company->id,
            'name' => 'Matrix Equipment',
            'code' => 'MX-'.uniqid(),
            'asset_type' => AssetType::Computer->value,
            'useful_life_years' => 5,
            'useful_life_months' => 60,
            'depreciation_method' => 'straight_line',
            'default_gl_code' => '1530',
            'accumulated_depreciation_gl_code' => '1550',
            'depreciation_expense_gl_code' => '6710',
            'is_active' => true,
        ]);
    }
}
