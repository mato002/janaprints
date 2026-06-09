<?php

namespace Tests\Feature\Assets;

use App\Enums\ApprovalRuleType;
use App\Enums\AssetDisposalStatus;
use App\Enums\AssetType;
use App\Enums\AssetWriteOffReason;
use App\Enums\AssetWriteOffStatus;
use App\Enums\CapitalizationCandidateStatus;
use App\Enums\FixedAssetStatus;
use App\Enums\JournalEntryType;
use App\Enums\JournalStatus;
use App\Enums\PostingEventCode;
use App\Enums\ProcurementItemClassification;
use App\Enums\PurchaseOrderStatus;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Platform\ApprovalRule;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use App\Models\User;
use App\Services\Assets\AssetCapitalizationService;
use App\Services\Assets\AssetDisposalAccountingService;
use App\Services\Assets\AssetWriteOffService;
use App\Support\Accounting\AccountingReversalService;
use App\Support\Accounting\AssetAcquisitionPostingService;
use App\Support\Assets\AssetDepreciationService;
use App\Support\Procurement\GoodsReceiptService;
use Database\Seeders\GlAccountTypeSeeder;
use Database\Seeders\InventoryFoundationSeeder;
use Database\Seeders\JanaPrintsAccountingPeriodsSeeder;
use Database\Seeders\JanaPrintsChartOfAccountsSeeder;
use Database\Seeders\JanaPrintsPostingEngineSeeder;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\PlatformConfigurationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AssetAccountingJournalTest extends TestCase
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
        $this->seed(InventoryFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
    }

    public function test_acquisition_journal_posts_balanced_gl_entries(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 80000]);

        $journal = app(AssetAcquisitionPostingService::class)->postAcquisition($asset, $user->id);

        $this->assertJournalEvent($journal, PostingEventCode::AssetAcquisitionPosted, 'fixed_asset', $asset->id);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), debit: 80000);
        $this->assertJournalHasLine($journal, $this->accountId('2100'), credit: 80000);
        $this->assertNotNull($asset->fresh()->posted_acquisition_journal_id);
    }

    public function test_capitalization_journal_posts_acquisition_on_capitalize(): void
    {
        $user = $this->financeUser();
        $candidate = $this->makeCandidate(['unit_cost' => 125000]);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 1,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Capitalized Press',
            'branch_id' => $candidate->branch_id,
        ], $user->id, true);

        $asset = $assets[0]->fresh();
        $journal = Journal::query()->findOrFail($asset->posted_acquisition_journal_id);

        $this->assertSame(CapitalizationCandidateStatus::Capitalized, $candidate->fresh()->status);
        $this->assertJournalEvent($journal, PostingEventCode::AssetAcquisitionPosted, 'fixed_asset', $asset->id);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), debit: 125000);
        $this->assertJournalHasLine($journal, $this->accountId('2100'), credit: 125000);
    }

    public function test_depreciation_journal_posts_expense_and_accumulated_depreciation(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 12000, 'useful_life_years' => 1]);

        $entry = AssetDepreciationService::runPeriod($asset, now()->startOfMonth()->toDateString(), $user->id);
        $journal = Journal::query()->findOrFail($entry->posted_journal_id);

        $this->assertJournalEvent($journal, PostingEventCode::AssetDepreciationPosted, 'asset_depreciation_entry', $entry->id);
        $this->assertJournalHasLine($journal, $this->accountId('6710'), debit: 1000);
        $this->assertJournalHasLine($journal, $this->accountId('1550'), credit: 1000);
    }

    public function test_write_off_journal_posts_loss_and_clears_asset_accounts(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 12000, 'useful_life_years' => 1]);
        AssetDepreciationService::runPeriod($asset->fresh(), now()->startOfMonth()->toDateString(), $user->id);
        $asset = $asset->fresh();

        $writeOff = app(AssetWriteOffService::class)->create($asset, [
            'reason' => AssetWriteOffReason::Obsolete->value,
            'write_off_date' => now()->toDateString(),
        ], $user->id);
        $posted = app(AssetWriteOffService::class)->post($writeOff, $user->id);
        $journal = Journal::query()->findOrFail($posted->posted_journal_id);

        $this->assertSame(AssetWriteOffStatus::Posted, $posted->status);
        $this->assertSame(FixedAssetStatus::Disposed, $posted->asset->status);
        $this->assertJournalEvent($journal, PostingEventCode::AssetWriteOffPosted, 'asset_write_off', $writeOff->id);
        $this->assertJournalHasLine($journal, $this->accountId('1550'), debit: 1000);
        $this->assertJournalHasLine($journal, $this->accountId('6700'), debit: 11000);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), credit: 12000);
    }

    public function test_disposal_journal_posts_full_retirement_entry(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 12000, 'useful_life_years' => 1]);
        AssetDepreciationService::runPeriod($asset->fresh(), now()->startOfMonth()->toDateString(), $user->id);

        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset->fresh(), [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 11000,
            'disposal_method' => 'sale',
        ], $user->id);
        $posted = app(AssetDisposalAccountingService::class)->post($disposal, $user->id);
        $journal = Journal::query()->findOrFail($posted->posted_journal_id);

        $this->assertSame(AssetDisposalStatus::Posted, $posted->status);
        $this->assertJournalEvent($journal, PostingEventCode::AssetDisposalPosted, 'asset_disposal', $disposal->id);
        $this->assertJournalHasLine($journal, $this->accountId('1550'), debit: 1000);
        $this->assertJournalHasLine($journal, $this->accountId('1210'), debit: 11000);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), credit: 12000);
    }

    public function test_disposal_gain_scenario_credits_gain_account(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 100000, 'accumulated_depreciation' => 40000, 'net_book_value' => 60000]);

        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset, [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 80000,
        ], $user->id);
        $posted = app(AssetDisposalAccountingService::class)->post($disposal, $user->id);
        $journal = Journal::query()->findOrFail($posted->posted_journal_id);

        $this->assertEqualsWithDelta(20000.0, (float) $posted->gain_loss_amount, 0.01);
        $this->assertJournalHasLine($journal, $this->accountId('1550'), debit: 40000);
        $this->assertJournalHasLine($journal, $this->accountId('1210'), debit: 80000);
        $this->assertJournalHasLine($journal, $this->accountId('4110'), credit: 20000);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), credit: 100000);
        $this->assertJournalHasLine($journal, $this->accountId('6700'), debit: 0);
    }

    public function test_disposal_loss_scenario_debits_loss_account(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 100000, 'accumulated_depreciation' => 40000, 'net_book_value' => 60000]);

        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset, [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 40000,
        ], $user->id);
        $posted = app(AssetDisposalAccountingService::class)->post($disposal, $user->id);
        $journal = Journal::query()->findOrFail($posted->posted_journal_id);

        $this->assertEqualsWithDelta(-20000.0, (float) $posted->gain_loss_amount, 0.01);
        $this->assertJournalHasLine($journal, $this->accountId('1550'), debit: 40000);
        $this->assertJournalHasLine($journal, $this->accountId('1210'), debit: 40000);
        $this->assertJournalHasLine($journal, $this->accountId('6700'), debit: 20000);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), credit: 100000);
        $this->assertJournalHasLine($journal, $this->accountId('4110'), credit: 0);
    }

    public function test_write_off_approval_gate_blocks_posting_until_approved(): void
    {
        $user = $this->financeUser();
        $this->seedApprovalRule(ApprovalRuleType::AssetWriteOffApproval);
        $asset = $this->makeAsset(['acquisition_cost' => 30000]);

        $writeOff = app(AssetWriteOffService::class)->create($asset, [
            'reason' => AssetWriteOffReason::Damaged->value,
            'write_off_date' => now()->toDateString(),
        ], $user->id);

        $this->assertSame(AssetWriteOffStatus::PendingApproval, $writeOff->status);

        $this->expectException(ValidationException::class);
        app(AssetWriteOffService::class)->post($writeOff, $user->id);
    }

    public function test_disposal_approval_gate_blocks_posting_until_approved(): void
    {
        $user = $this->financeUser();
        $this->seedApprovalRule(ApprovalRuleType::AssetDisposalApproval);
        $asset = $this->makeAsset(['acquisition_cost' => 75000]);

        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset, [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 50000,
        ], $user->id);

        $this->assertSame(AssetDisposalStatus::PendingApproval, $disposal->status);

        $this->expectException(ValidationException::class);
        app(AssetDisposalAccountingService::class)->post($disposal, $user->id);
    }

    public function test_approved_write_off_posts_journal_after_approval(): void
    {
        $user = $this->financeUser();
        $this->seedApprovalRule(ApprovalRuleType::AssetWriteOffApproval);
        $asset = $this->makeAsset(['acquisition_cost' => 30000]);

        $writeOff = app(AssetWriteOffService::class)->create($asset, [
            'reason' => AssetWriteOffReason::Damaged->value,
            'write_off_date' => now()->toDateString(),
        ], $user->id);
        $approved = app(AssetWriteOffService::class)->approve($writeOff, $user->id);
        $posted = app(AssetWriteOffService::class)->post($approved, $user->id);

        $this->assertSame(AssetWriteOffStatus::Posted, $posted->status);
        $this->assertNotNull($posted->posted_journal_id);

        $journal = Journal::query()->findOrFail($posted->posted_journal_id);
        $this->assertJournalEvent($journal, PostingEventCode::AssetWriteOffPosted, 'asset_write_off', $writeOff->id);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), credit: 30000);
        $this->assertJournalHasLine($journal, $this->accountId('6700'), debit: 30000);
    }

    public function test_approved_disposal_posts_journal_after_approval(): void
    {
        $user = $this->financeUser();
        $this->seedApprovalRule(ApprovalRuleType::AssetDisposalApproval);
        $asset = $this->makeAsset(['acquisition_cost' => 75000, 'accumulated_depreciation' => 15000, 'net_book_value' => 60000]);

        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset, [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 50000,
        ], $user->id);
        $approved = app(AssetDisposalAccountingService::class)->approve($disposal, $user->id);
        $posted = app(AssetDisposalAccountingService::class)->post($approved, $user->id);

        $this->assertSame(AssetDisposalStatus::Posted, $posted->status);
        $this->assertNotNull($posted->posted_journal_id);

        $journal = Journal::query()->findOrFail($posted->posted_journal_id);
        $this->assertJournalEvent($journal, PostingEventCode::AssetDisposalPosted, 'asset_disposal', $disposal->id);
        $this->assertJournalHasLine($journal, $this->accountId('1550'), debit: 15000);
        $this->assertJournalHasLine($journal, $this->accountId('1210'), debit: 50000);
        $this->assertJournalHasLine($journal, $this->accountId('6700'), debit: 10000);
        $this->assertJournalHasLine($journal, $this->accountId('1530'), credit: 75000);
    }

    public function test_acquisition_duplicate_posting_is_idempotent(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 45000]);

        $first = app(AssetAcquisitionPostingService::class)->postAcquisition($asset, $user->id);
        $second = app(AssetAcquisitionPostingService::class)->postAcquisition($asset->fresh(), $user->id);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Journal::query()
            ->where('source_type', 'fixed_asset')
            ->where('source_id', $asset->id)
            ->where('posting_event', PostingEventCode::AssetAcquisitionPosted->value)
            ->count());
    }

    public function test_closed_period_blocks_write_off_posting(): void
    {
        $user = $this->financeUser();
        $period = AccountingPeriod::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();
        app(\App\Support\Accounting\AccountingPeriodService::class)->close($period, $user->id);

        $asset = $this->makeAsset(['acquisition_cost' => 25000]);
        $writeOff = app(AssetWriteOffService::class)->create($asset, [
            'reason' => AssetWriteOffReason::Damaged->value,
            'write_off_date' => now()->toDateString(),
        ], $user->id);

        $this->expectException(ValidationException::class);
        app(AssetWriteOffService::class)->post($writeOff, $user->id);
    }

    public function test_closed_period_blocks_disposal_posting(): void
    {
        $user = $this->financeUser();
        $period = AccountingPeriod::query()
            ->where('company_id', $this->company->id)
            ->where('is_current', true)
            ->firstOrFail();
        app(\App\Support\Accounting\AccountingPeriodService::class)->close($period, $user->id);

        $asset = $this->makeAsset(['acquisition_cost' => 50000]);
        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset, [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 30000,
        ], $user->id);

        $this->expectException(ValidationException::class);
        app(AssetDisposalAccountingService::class)->post($disposal, $user->id);
    }

    public function test_acquisition_journal_reversal_restores_gl_balances(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 45000]);
        app(AssetAcquisitionPostingService::class)->postAcquisition($asset, $user->id);

        $reversal = app(AccountingReversalService::class)->reverseBySource(
            $this->company->id,
            'fixed_asset',
            $asset->id,
            $user->id,
            event: PostingEventCode::AssetAcquisitionPosted,
        );

        $this->assertSame(JournalEntryType::Reversal, $reversal->entry_type);
        $this->assertSame(JournalStatus::Posted, $reversal->status);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1530'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('2100'), 0.01);
    }

    public function test_depreciation_journal_reversal_restores_gl_balances(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 12000, 'useful_life_years' => 1]);
        $entry = AssetDepreciationService::runPeriod($asset, now()->startOfMonth()->toDateString(), $user->id);

        $reversal = app(AccountingReversalService::class)->reverseBySource(
            $this->company->id,
            'asset_depreciation_entry',
            $entry->id,
            $user->id,
            event: PostingEventCode::AssetDepreciationPosted,
        );

        $this->assertSame(JournalEntryType::Reversal, $reversal->entry_type);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('6710'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1550'), 0.01);
    }

    public function test_write_off_journal_reversal_restores_gl_balances(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 20000]);
        $writeOff = app(AssetWriteOffService::class)->create($asset, [
            'reason' => AssetWriteOffReason::Obsolete->value,
            'write_off_date' => now()->toDateString(),
        ], $user->id);
        $posted = app(AssetWriteOffService::class)->post($writeOff, $user->id);

        $reversal = app(AccountingReversalService::class)->reverseBySource(
            $this->company->id,
            'asset_write_off',
            $posted->id,
            $user->id,
            event: PostingEventCode::AssetWriteOffPosted,
        );

        $this->assertSame(JournalEntryType::Reversal, $reversal->entry_type);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('6700'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1530'), 0.01);
    }

    public function test_disposal_journal_reversal_restores_gl_balances(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 60000, 'accumulated_depreciation' => 10000, 'net_book_value' => 50000]);

        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset, [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 45000,
        ], $user->id);
        $posted = app(AssetDisposalAccountingService::class)->post($disposal, $user->id);

        $reversal = app(AccountingReversalService::class)->reverseBySource(
            $this->company->id,
            'asset_disposal',
            $posted->id,
            $user->id,
            event: PostingEventCode::AssetDisposalPosted,
        );

        $this->assertSame(JournalEntryType::Reversal, $reversal->entry_type);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1210'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('6700'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1530'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1550'), 0.01);
    }

    public function test_disposal_gain_reversal_restores_gain_account_balance(): void
    {
        $user = $this->financeUser();
        $asset = $this->makeAsset(['acquisition_cost' => 100000, 'accumulated_depreciation' => 40000, 'net_book_value' => 60000]);

        $disposal = app(AssetDisposalAccountingService::class)->dispose($asset, [
            'disposal_date' => now()->toDateString(),
            'disposal_proceeds' => 80000,
        ], $user->id);
        $posted = app(AssetDisposalAccountingService::class)->post($disposal, $user->id);

        $reversal = app(AccountingReversalService::class)->reverseBySource(
            $this->company->id,
            'asset_disposal',
            $posted->id,
            $user->id,
            event: PostingEventCode::AssetDisposalPosted,
        );

        $this->assertSame(JournalEntryType::Reversal, $reversal->entry_type);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('4110'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1210'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1530'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1550'), 0.01);
    }

    public function test_capitalization_journal_reversal_restores_gl_balances(): void
    {
        $user = $this->financeUser();
        $candidate = $this->makeCandidate(['unit_cost' => 95000]);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 1,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Reversal Test Asset',
            'branch_id' => $candidate->branch_id,
        ], $user->id, true);

        $asset = $assets[0]->fresh();

        $reversal = app(AccountingReversalService::class)->reverseBySource(
            $this->company->id,
            'fixed_asset',
            $asset->id,
            $user->id,
            event: PostingEventCode::AssetAcquisitionPosted,
        );

        $this->assertSame(JournalEntryType::Reversal, $reversal->entry_type);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('1530'), 0.01);
        $this->assertEqualsWithDelta(0.0, $this->accountBalance('2100'), 0.01);
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

    protected function seedApprovalRule(ApprovalRuleType $ruleType): void
    {
        ApprovalRule::query()->updateOrCreate(
            [
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'rule_type' => $ruleType->value,
            ],
            [
                'is_enabled' => true,
                'min_approvers' => 1,
                'settings_json' => [
                    'tiers' => [
                        ['threshold_amount' => 0, 'approver_role' => 'Company Admin'],
                    ],
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeAsset(array $overrides = []): FixedAsset
    {
        $category = $this->makeCategory();

        return FixedAsset::query()->create(array_merge([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'asset_category_id' => $category->id,
            'asset_number' => 'AST-'.uniqid(),
            'asset_name' => 'Journal Test Asset',
            'acquisition_date' => now()->startOfMonth(),
            'capitalization_date' => now()->startOfMonth(),
            'acquisition_cost' => 100000,
            'residual_value' => 0,
            'useful_life_years' => 5,
            'depreciation_method' => 'straight_line',
            'depreciation_start_date' => now()->startOfMonth(),
            'accumulated_depreciation' => 0,
            'net_book_value' => 100000,
            'status' => FixedAssetStatus::Active,
        ], $overrides));
    }

    protected function makeCategory(): AssetCategory
    {
        return AssetCategory::query()->create([
            'company_id' => $this->company->id,
            'name' => 'Equipment',
            'code' => 'EQP-'.uniqid(),
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeCandidate(array $overrides = []): AssetCapitalizationCandidate
    {
        $user = $this->financeUser();
        $category = $this->makeCategory();
        $vendor = Vendor::factory()->create(['company_id' => $this->company->id]);
        $warehouse = \App\Models\Inventory\Warehouse::query()->where('company_id', $this->company->id)->firstOrFail();
        $unitCost = (float) ($overrides['unit_cost'] ?? 100000);

        $order = PurchaseOrder::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'vendor_id' => $vendor->id,
            'po_number' => 'PO-JNL-'.uniqid(),
            'order_date' => now()->toDateString(),
            'status' => PurchaseOrderStatus::Sent,
            'subtotal' => $unitCost,
            'total_amount' => $unitCost,
            'prepared_by' => $user->id,
        ]);

        $poItem = $order->items()->create([
            'description' => 'Capital Asset',
            'quantity' => 1,
            'unit_cost' => $unitCost,
            'line_total' => $unitCost,
            'item_classification' => ProcurementItemClassification::FixedAsset,
            'asset_category_id' => $category->id,
        ]);

        $grn = GoodsReceipt::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'receipt_number' => 'GRN-JNL-'.uniqid(),
            'receipt_date' => now()->toDateString(),
            'status' => \App\Enums\GoodsReceiptStatus::Draft,
            'received_by' => $user->id,
        ]);

        $grn->items()->create([
            'purchase_order_item_id' => $poItem->id,
            'item_classification' => ProcurementItemClassification::FixedAsset,
            'asset_category_id' => $category->id,
            'quantity_received' => 1,
            'unit_cost' => $unitCost,
        ]);

        GoodsReceiptService::post($grn->fresh(['items']), $user->id);

        return AssetCapitalizationCandidate::query()->where('goods_receipt_id', $grn->id)->firstOrFail();
    }

    protected function accountId(string $code): int
    {
        return (int) GlAccount::query()
            ->where('company_id', $this->company->id)
            ->where('code', $code)
            ->value('id');
    }

    protected function accountBalance(string $code): float
    {
        return (float) GlAccount::query()
            ->where('company_id', $this->company->id)
            ->where('code', $code)
            ->value('current_balance');
    }

    protected function assertJournalEvent(Journal $journal, PostingEventCode $event, string $sourceType, int $sourceId): void
    {
        $this->assertSame(JournalStatus::Posted, $journal->status);
        $this->assertSame($event->value, $journal->posting_event);
        $this->assertSame($sourceType, $journal->source_type);
        $this->assertSame($sourceId, $journal->source_id);
        $this->assertTrue($journal->isBalanced());
    }

    protected function assertJournalHasLine(Journal $journal, int $accountId, float $debit = 0, float $credit = 0): void
    {
        $journal->load('lines');
        $line = $journal->lines->firstWhere('gl_account_id', $accountId);

        if ($debit <= 0 && $credit <= 0) {
            $this->assertNull($line, "Journal should not contain line for account {$accountId}");

            return;
        }

        $this->assertNotNull($line, "Journal missing line for account {$accountId}");
        $this->assertEqualsWithDelta($debit, (float) $line->debit, 0.01);
        $this->assertEqualsWithDelta($credit, (float) $line->credit, 0.01);
    }
}
