<?php

namespace App\Support\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\ProductionJobCardService;
use Illuminate\Validation\ValidationException;

class SalesOrderProductionBridgeService
{
    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    public function ensureJobCard(SalesOrder $salesOrder, int $userId, array $attributes = []): ProductionJobCard
    {
        $salesOrder->loadMissing('jobCard');

        if ($salesOrder->jobCard) {
            return $salesOrder->jobCard;
        }

        return ProductionJobCardService::createFromSalesOrder($salesOrder, $userId, $attributes);
    }

    /**
     * Create a job card when prerequisites are met; otherwise return null.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function tryEnsureJobCard(SalesOrder $salesOrder, int $userId, array $attributes = []): ?ProductionJobCard
    {
        $salesOrder->loadMissing('jobCard');

        if ($salesOrder->jobCard) {
            return $salesOrder->jobCard;
        }

        try {
            return ProductionJobCardService::createFromSalesOrder($salesOrder, $userId, $attributes);
        } catch (ValidationException) {
            return null;
        }
    }

    public function targetSalesOrderStatus(ProductionJobCardStatus $jobCardStatus): ?SalesOrderStatus
    {
        return match ($jobCardStatus) {
            ProductionJobCardStatus::Draft => SalesOrderStatus::ReadyForProduction,
            ProductionJobCardStatus::Queued => SalesOrderStatus::ReadyForProduction,
            ProductionJobCardStatus::InProduction => SalesOrderStatus::InProduction,
            ProductionJobCardStatus::QualityCheck => SalesOrderStatus::InProduction,
            ProductionJobCardStatus::Rework => SalesOrderStatus::InProduction,
            ProductionJobCardStatus::Completed => SalesOrderStatus::Completed,
            ProductionJobCardStatus::ReadyForDispatch => SalesOrderStatus::Completed,
            ProductionJobCardStatus::OnHold => SalesOrderStatus::OnHold,
            ProductionJobCardStatus::Cancelled => SalesOrderStatus::Cancelled,
            default => null,
        };
    }

    public function syncSalesOrderStatus(ProductionJobCard $jobCard, ProductionJobCardStatus $jobCardStatus): void
    {
        $salesOrder = $jobCard->salesOrder;

        if ($salesOrder === null) {
            return;
        }

        $target = $this->targetSalesOrderStatus($jobCardStatus);

        if ($target === null) {
            return;
        }

        $this->advanceSalesOrderTo($salesOrder, $target);
    }

    public function syncSalesOrderFromDelivery(DeliveryNote $note): void
    {
        app(\App\Support\Dispatch\DeliverySalesOrderSyncService::class)
            ->syncFromDeliveredNote($note);
    }

    public function isSynchronized(ProductionJobCard $jobCard): bool
    {
        $salesOrder = $jobCard->salesOrder;

        if ($salesOrder === null) {
            return true;
        }

        $expected = $this->targetSalesOrderStatus($jobCard->status);

        if ($expected === null) {
            return true;
        }

        return $salesOrder->status === $expected;
    }

    /**
     * @throws ValidationException
     */
    public function assertSynchronized(ProductionJobCard $jobCard): void
    {
        if ($this->isSynchronized($jobCard)) {
            return;
        }

        $salesOrder = $jobCard->salesOrder;
        $expected = $this->targetSalesOrderStatus($jobCard->status);

        throw ValidationException::withMessages([
            'status' => __(
                'Sales order status :actual does not match job card status :job (expected :expected).',
                [
                    'actual' => $salesOrder?->status->value ?? '—',
                    'job' => $jobCard->status->value,
                    'expected' => $expected?->value ?? '—',
                ],
            ),
        ]);
    }

    public function advanceSalesOrderTo(SalesOrder $salesOrder, SalesOrderStatus $target): void
    {
        $salesOrder->refresh();

        if ($salesOrder->status === $target) {
            return;
        }

        if ($salesOrder->status->canTransitionTo($target)) {
            $salesOrder->transitionTo($target);

            return;
        }

        foreach ($this->productionPipelineSteps() as $step) {
            $salesOrder->refresh();

            if ($salesOrder->status === $target) {
                return;
            }

            if (! $this->shouldAdvanceToward($salesOrder->status, $target, $step)) {
                continue;
            }

            if ($salesOrder->status->canTransitionTo($step)) {
                $salesOrder->transitionTo($step);
            }
        }

        $salesOrder->refresh();

        if ($salesOrder->status->canTransitionTo($target)) {
            $salesOrder->transitionTo($target);
        }
    }

    /**
     * @return list<SalesOrderStatus>
     */
    protected function productionPipelineSteps(): array
    {
        return [
            SalesOrderStatus::ReadyForProduction,
            SalesOrderStatus::InProduction,
            SalesOrderStatus::Completed,
            SalesOrderStatus::Delivered,
        ];
    }

    protected function shouldAdvanceToward(
        SalesOrderStatus $current,
        SalesOrderStatus $target,
        SalesOrderStatus $step,
    ): bool {
        $order = [
            SalesOrderStatus::Draft->value => 0,
            SalesOrderStatus::Confirmed->value => 1,
            SalesOrderStatus::ReadyForProduction->value => 2,
            SalesOrderStatus::InProduction->value => 3,
            SalesOrderStatus::Completed->value => 4,
            SalesOrderStatus::Delivered->value => 5,
            SalesOrderStatus::Closed->value => 6,
            SalesOrderStatus::OnHold->value => -1,
            SalesOrderStatus::Cancelled->value => -1,
        ];

        $currentRank = $order[$current->value] ?? -1;
        $targetRank = $order[$target->value] ?? -1;
        $stepRank = $order[$step->value] ?? -1;

        if ($currentRank < 0 || $targetRank < 0 || $stepRank < 0) {
            return false;
        }

        return $stepRank > $currentRank && $stepRank <= $targetRank;
    }
}
