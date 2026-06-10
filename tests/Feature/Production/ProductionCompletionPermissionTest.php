<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionOutput;
use App\Services\Production\ProductionCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class ProductionCompletionPermissionTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCompletionEnvironment();
    }

    public function test_unauthorized_user_cannot_post_output(): void
    {
        [, , , , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
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
            ->post(route('admin.production.job-cards.outputs.store', $jobCard), [
                'finished_inventory_item_id' => $finishedItem->id,
                'quantity_completed' => 1,
            ])
            ->assertForbidden();

        $this->assertSame(0, ProductionOutput::query()->count());
    }

    public function test_manual_unit_cost_requires_permission(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
        ]);

        $this->expectException(ValidationException::class);
        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
            'unit_cost' => 99.99,
        ], $user->id, false);
    }

    public function test_manual_unit_cost_allowed_with_permission(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion([
            'production.view',
            'production.outputs.view',
            'production.outputs.post',
            'production.outputs.manual-cost',
            'inventory.view',
            'inventory.receive',
            'inventory.issue',
        ]);

        $jobCard->materialConsumptions()->delete();

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
            'unit_cost' => 75.5,
        ], $user->id, true);

        $this->assertEquals(75.5, (float) $output->unit_cost);
    }
}
