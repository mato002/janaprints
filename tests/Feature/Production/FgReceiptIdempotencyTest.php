<?php

namespace Tests\Feature\Production;

use App\Enums\InventoryMovementType;
use App\Models\Inventory\InventoryMovement;
use App\Models\Production\ProductionOutput;
use App\Services\Production\ProductionCompletionService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class FgReceiptIdempotencyTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCompletionEnvironment();
    }

    public function test_production_output_has_unique_lifecycle_receipt_key_on_movement(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $this->assertDatabaseHas('inventory_movements', [
            'reference_type' => ProductionOutput::class,
            'reference_id' => $output->id,
            'movement_type' => InventoryMovementType::FinishedGoodsReceipt->value,
            'lifecycle_receipt_key' => ProductionCompletionService::fgReceiptKey($output->id),
        ]);
    }

    public function test_duplicate_fg_movement_blocked_at_database_level(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        $output = app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $movement = InventoryMovement::query()->findOrFail($output->inventory_movement_id);
        $duplicate = $movement->replicate();
        $duplicate->quantity = 1;

        $this->expectException(QueryException::class);
        $duplicate->save();
    }

    public function test_posted_job_marker_enforces_one_posted_output_per_job(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $this->expectException(QueryException::class);

        ProductionOutput::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 2,
            'completion_status' => 'posted',
            'posted_job_marker' => $jobCard->id,
        ]);
    }

    public function test_concurrent_completion_second_attempt_blocked_by_service(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $service = app(ProductionCompletionService::class);

        DB::transaction(function () use ($service, $jobCard, $finishedItem, $user) {
            $service->post($jobCard, [
                'finished_inventory_item_id' => $finishedItem->id,
                'quantity_completed' => 1,
            ], $user->id);
        });

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->post($jobCard->fresh(), [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);
    }
}
