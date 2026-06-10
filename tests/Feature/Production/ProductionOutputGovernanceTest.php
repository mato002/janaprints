<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionOutputStatus;
use App\Models\Production\ProductionOutput;
use App\Services\Production\ProductionCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Production\Concerns\InteractsWithProductionCompletion;
use Tests\TestCase;

class ProductionOutputGovernanceTest extends TestCase
{
    use InteractsWithProductionCompletion;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedProductionCompletionEnvironment();
    }

    public function test_second_fg_completion_blocked_for_same_job(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $service = app(ProductionCompletionService::class);

        $service->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $this->expectException(ValidationException::class);

        try {
            $service->post($jobCard->fresh(), [
                'finished_inventory_item_id' => $finishedItem->id,
                'quantity_completed' => 2,
            ], $user->id);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('production_job_card_id', $exception->errors());
            $this->assertStringContainsString(
                'already been completed into Finished Goods',
                implode(' ', $exception->errors()['production_job_card_id']),
            );

            throw $exception;
        }
    }

    public function test_duplicate_output_posting_blocked_with_different_quantity(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $service = app(ProductionCompletionService::class);

        $service->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 5,
        ], $user->id);

        $this->expectException(ValidationException::class);
        $service->post($jobCard->fresh(), [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);
    }

    public function test_eligibility_reports_blocker_when_job_already_posted(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();

        app(ProductionCompletionService::class)->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        $eligibility = app(ProductionCompletionService::class)->eligibility($jobCard->fresh());

        $this->assertFalse($eligibility['eligible']);
        $this->assertTrue(
            collect($eligibility['blockers'])->contains(
                fn (string $blocker) => str_contains($blocker, 'already been completed into Finished Goods'),
            ),
        );
    }

    public function test_only_one_posted_output_record_per_job(): void
    {
        [, , $user, , $finishedItem, , $jobCard] = $this->readyJobForCompletion();
        $service = app(ProductionCompletionService::class);

        $service->post($jobCard, [
            'finished_inventory_item_id' => $finishedItem->id,
            'quantity_completed' => 1,
        ], $user->id);

        try {
            $service->post($jobCard->fresh(), [
                'finished_inventory_item_id' => $finishedItem->id,
                'quantity_completed' => 1,
            ], $user->id);
        } catch (ValidationException) {
            // expected
        }

        $this->assertSame(1, ProductionOutput::query()
            ->where('production_job_card_id', $jobCard->id)
            ->where('completion_status', ProductionOutputStatus::Posted)
            ->count());
    }
}
