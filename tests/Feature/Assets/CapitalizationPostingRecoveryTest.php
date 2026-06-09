<?php

namespace Tests\Feature\Assets;

use App\Enums\AssetAcquisitionAccountingStatus;
use App\Enums\AssetAcquisitionSource;
use App\Enums\PostingEventCode;
use App\Models\Accounting\Journal;
use App\Models\Assets\FixedAsset;
use App\Models\User;
use App\Services\Assets\AssetCapitalizationPostingRecoveryService;
use App\Services\Assets\AssetCapitalizationReconciliationService;
use App\Services\Assets\AssetCapitalizationService;
use Illuminate\Validation\ValidationException;

class CapitalizationPostingRecoveryTest extends AssetCapitalizationTest
{
    public function test_recovery_queue_lists_unposted_capitalized_assets(): void
    {
        $user = $this->acquisitionUser();
        $asset = $this->unpostedCapitalizedAsset($user);

        $this->actingAs($user)
            ->get(route('admin.assets.acquisitions.recovery.index'))
            ->assertOk()
            ->assertSee($asset->asset_name, false)
            ->assertSee(__('Capitalization Recovery Queue'), false)
            ->assertSee(__('Post To GL'), false);
    }

    public function test_recovery_posting_creates_acquisition_journal(): void
    {
        $user = $this->acquisitionUser();
        $asset = $this->unpostedCapitalizedAsset($user);

        $this->actingAs($user)
            ->post(route('admin.assets.acquisitions.recovery.post', $asset))
            ->assertRedirect(route('admin.assets.acquisitions.recovery.index'));

        $asset = $asset->fresh();
        $this->assertSame(AssetAcquisitionAccountingStatus::Posted, $asset->acquisition_accounting_status);
        $this->assertNotNull($asset->posted_acquisition_journal_id);

        $journal = Journal::query()->findOrFail($asset->posted_acquisition_journal_id);
        $this->assertSame(PostingEventCode::AssetAcquisitionPosted->value, $journal->posting_event);
        $this->assertTrue($journal->isBalanced());
    }

    public function test_duplicate_posting_is_prevented(): void
    {
        $user = $this->acquisitionUser();
        $asset = $this->unpostedCapitalizedAsset($user);
        $recovery = app(AssetCapitalizationPostingRecoveryService::class);

        $recovery->post($asset, $user->id);

        $this->assertSame(1, Journal::query()->where('source_type', 'fixed_asset')->where('source_id', $asset->id)->count());

        $this->expectException(ValidationException::class);
        $recovery->post($asset->fresh(), $user->id);
    }

    public function test_reconciliation_clears_capitalized_not_posted_after_recovery(): void
    {
        $user = $this->acquisitionUser();
        $asset = $this->unpostedCapitalizedAsset($user);

        $before = app(AssetCapitalizationReconciliationService::class)->run($user->company_id, $user->id);
        $this->assertGreaterThan(0, $before->capitalized_not_posted_count);

        app(AssetCapitalizationPostingRecoveryService::class)->post($asset, $user->id);

        $after = app(AssetCapitalizationReconciliationService::class)->run($user->company_id, $user->id);
        $this->assertSame(0, $after->capitalized_not_posted_count);
        $this->assertNull(collect($after->variance_details)->firstWhere('type', 'capitalized_not_posted'));
    }

    public function test_recovery_posting_requires_permission(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->makeCandidate()->company_id,
            'is_active' => true,
        ]);
        $viewer->givePermissionTo(['assets.acquisition.view', 'assets.capitalize']);

        $capitalizer = $this->acquisitionUser();
        $asset = $this->unpostedCapitalizedAsset($capitalizer);

        $this->actingAs($viewer)
            ->post(route('admin.assets.acquisitions.recovery.post', $asset))
            ->assertForbidden();
    }

    public function test_audit_history_page_shows_finance_timeline(): void
    {
        $user = $this->acquisitionUser();
        $asset = $this->unpostedCapitalizedAsset($user);

        app(AssetCapitalizationPostingRecoveryService::class)->post($asset, $user->id);

        $this->actingAs($user)
            ->get(route('admin.assets.acquisitions.recovery.audit', $asset))
            ->assertOk()
            ->assertSee(__('Capitalization acquisition journal posted'), false);
    }

    protected function unpostedCapitalizedAsset(User $user): FixedAsset
    {
        $candidate = $this->makeCandidate(['unit_cost' => 88000]);

        $assets = app(AssetCapitalizationService::class)->capitalize($candidate, [
            'quantity' => 1,
            'asset_category_id' => $candidate->asset_category_id,
            'asset_name' => 'Recovery Queue Asset',
            'branch_id' => $candidate->branch_id,
        ], $user->id, false);

        $asset = $assets[0]->fresh();
        $this->assertSame(AssetAcquisitionSource::Procurement, $asset->acquisition_source);
        $this->assertNull($asset->posted_acquisition_journal_id);

        return $asset;
    }
}
