<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionOutputStatus;
use App\Models\Production\ProductionOutput;
use App\Services\Production\ProductionCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class ProductionJobCardCompletionUiTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCompletionEnvironment();
    }

    public function test_job_card_shows_complete_to_finished_goods_action_when_eligible(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
        ]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']))
            ->assertOk()
            ->assertSee(__('Post to finished goods'), false)
            ->assertSee($finishedItem->sku, false);
    }

    public function test_outputs_tab_lists_posted_output(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
        ]);

        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']))
            ->assertOk()
            ->assertSee(__('Output history'), false)
            ->assertSee($finishedItem->sku, false)
            ->assertSee(ProductionOutputStatus::Posted->label(), false);
    }

    public function test_action_hidden_when_user_lacks_post_permission(): void
    {
        [, , , , , , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
        ]);

        $viewer = $this->completionUser(
            Company::query()->findOrFail($jobCard->company_id),
            Branch::query()->findOrFail($jobCard->branch_id),
            ['production.view', 'production.outputs.view'],
            'Storekeeper',
        );

        $this->actingAs($viewer)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'overview']))
            ->assertOk()
            ->assertDontSee(__('Post to finished goods'), false);
    }

    public function test_action_not_available_for_cancelled_job(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
        ]);

        $jobCard->update(['status' => ProductionJobCardStatus::Cancelled]);

        $this->actingAs($user)
            ->post(route('admin.production.job-cards.outputs.store', $jobCard), [
                'finished_inventory_item_id' => $finishedItem->id,
                'quantity_completed' => 1,
            ])
            ->assertSessionHasErrors();

        $this->assertSame(0, ProductionOutput::query()->count());
    }

    public function test_wrong_stock_role_offers_store_classify_action_on_finished_goods_tab(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
            'catalogue.view',
            'catalogue.edit',
            'inventory.classification.manage',
        ]);

        $finishedItem->update([
            'item_name' => 'Ncr white',
            'stock_role' => \App\Enums\InventoryStockRole::RawMaterial,
        ]);
        $jobCard->update(['inventory_item_id' => $finishedItem->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']))
            ->assertOk()
            ->assertSee(__('Set as finished good'), false)
            ->assertDontSee(__('Open product'), false);
    }

    public function test_wrong_stock_role_asks_store_when_user_cannot_classify(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
            'catalogue.view',
        ]);

        $finishedItem->update([
            'item_name' => 'Ncr white',
            'stock_role' => \App\Enums\InventoryStockRole::RawMaterial,
        ]);
        $jobCard->update(['inventory_item_id' => $finishedItem->id]);

        $this->actingAs($user)
            ->get(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']))
            ->assertOk()
            ->assertSee(__('Ask store to open :item in Inventory and set stock role to Finished good.', [
                'item' => 'Ncr white',
            ]), false)
            ->assertSee(__('View product'), false)
            ->assertDontSee(__('Set as finished good'), false);
    }

    public function test_classifying_product_from_job_returns_to_finished_goods_tab(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
            'catalogue.view',
            'catalogue.edit',
            'inventory.classification.manage',
        ]);

        $finishedItem->update(['stock_role' => \App\Enums\InventoryStockRole::RawMaterial]);
        $jobCard->update(['inventory_item_id' => $finishedItem->id]);

        $this->actingAs($user)
            ->post(route('admin.inventory.items.classify-finished-good', $finishedItem), [
                'production_job_card_id' => $jobCard->id,
            ])
            ->assertRedirect(route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']));

        $this->assertSame(\App\Enums\InventoryStockRole::FinishedGood, $finishedItem->fresh()->stock_role);
    }
}
