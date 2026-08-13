<?php

namespace App\Support\Sales;

use App\Enums\ArtworkRequestStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Services\Production\ProductionReleaseReadinessService;
use App\Support\Production\SalesOrderProductionBridgeService;
use Illuminate\Validation\ValidationException;

class SalesOrderWorkflowService
{
    public function __construct(
        protected SalesOrderProductionBridgeService $bridge,
        protected ProductionReleaseReadinessService $releaseReadiness,
    ) {}

    public function tryReleaseToProduction(SalesOrder $salesOrder, int $userId): bool
    {
        try {
            $this->releaseToProduction($salesOrder, $userId);

            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    public function releaseToProduction(SalesOrder $salesOrder, int $userId): ProductionJobCard
    {
        $salesOrder->refresh();

        $user = \App\Models\User::query()->find($userId);
        $this->releaseReadiness->assertReady($salesOrder, $user);

        if (! $salesOrder->production_destination) {
            throw ValidationException::withMessages([
                'production_destination' => __('Choose Digital, Offset, or Outsourced before sending this order to production.'),
            ]);
        }

        if ($salesOrder->status === SalesOrderStatus::Confirmed) {
            if (! $salesOrder->status->canTransitionTo(SalesOrderStatus::ReadyForProduction)) {
                throw ValidationException::withMessages([
                    'workflow' => __('This sales order cannot be sent to production in its current status.'),
                ]);
            }

            $salesOrder->transitionTo(SalesOrderStatus::ReadyForProduction);
        }

        if (! in_array($salesOrder->status, [SalesOrderStatus::ReadyForProduction, SalesOrderStatus::Confirmed], true)) {
            throw ValidationException::withMessages([
                'workflow' => __('Only confirmed orders can be sent to production.'),
            ]);
        }

        return $this->bridge->activateJobForProduction(
            $this->bridge->ensureJobCard($salesOrder, $userId),
            $userId,
        );
    }

    /**
     * @return array{
     *     hint: string|null,
     *     pipeline: list<array{key: string, label: string, state: string}>,
     *     can_confirm: bool,
     *     can_release: bool,
     *     can_close: bool,
     * }
     */
    public function present(SalesOrder $salesOrder): array
    {
        $salesOrder->loadMissing('jobCard');

        return [
            'hint' => $this->hint($salesOrder),
            'pipeline' => $this->pipeline($salesOrder),
            'can_confirm' => $salesOrder->status->canTransitionTo(SalesOrderStatus::Confirmed),
            'can_release' => $this->canRelease($salesOrder),
            'can_close' => $salesOrder->status->canTransitionTo(SalesOrderStatus::Closed),
        ];
    }

    public function canRelease(SalesOrder $salesOrder): bool
    {
        if (! in_array($salesOrder->status, [
            SalesOrderStatus::Confirmed,
            SalesOrderStatus::ReadyForProduction,
        ], true)) {
            return false;
        }

        if ($salesOrder->jobCard) {
            return $salesOrder->jobCard->status === ProductionJobCardStatus::Draft;
        }

        return true;
    }

    protected function hint(SalesOrder $salesOrder): ?string
    {
        if ($salesOrder->status === SalesOrderStatus::Confirmed && ! $salesOrder->jobCard) {
            $artworkHint = $this->directOrderArtworkHint($salesOrder);

            if ($artworkHint !== null) {
                return $artworkHint;
            }
        }

        return match ($salesOrder->status) {
            SalesOrderStatus::Draft => __('Confirm this order to accept it commercially. Production will start automatically when artwork and prerequisites are ready.'),
            SalesOrderStatus::Confirmed => $salesOrder->jobCard
                ? null
                : __('Send this order to production to create a job card. Further progress is tracked in Production and Dispatch.'),
            SalesOrderStatus::ReadyForProduction,
            SalesOrderStatus::InProduction,
            SalesOrderStatus::Completed,
            SalesOrderStatus::ReadyForDispatch => __('Production and dispatch teams drive the next steps. Status updates here automatically.'),
            SalesOrderStatus::Delivered => __('Delivery is complete. Close the order once invoicing and payment requirements are met.'),
            SalesOrderStatus::OnHold => __('This order is on hold. Resume it to continue.'),
            SalesOrderStatus::Closed => __('This order is closed.'),
            SalesOrderStatus::Cancelled => __('This order was cancelled.'),
            default => null,
        };
    }

    protected function directOrderArtworkHint(SalesOrder $salesOrder): ?string
    {
        if (! $salesOrder->is_direct_order) {
            return null;
        }

        $salesOrder->loadMissing(['inventoryItem', 'artworkRequest']);

        $artworkOk = ($salesOrder->uses_existing_artwork && $salesOrder->customer_artwork_id)
            || ($salesOrder->artworkRequest && $salesOrder->artworkRequest->status === ArtworkRequestStatus::Approved);

        if ($artworkOk) {
            return null;
        }

        $requiresArtwork = app(DirectCustomerSalesOrderService::class)
            ->productRequiresArtwork($salesOrder->inventoryItem);

        if (! $requiresArtwork) {
            return null;
        }

        return __('This direct order needs artwork on the print specification before production can start. Upload artwork to the specification, or create an artwork request for design work.');
    }

    /**
     * @return list<array{key: string, label: string, state: string}>
     */
    protected function pipeline(SalesOrder $salesOrder): array
    {
        $rank = $this->statusRank($salesOrder->status);

        $steps = [
            ['key' => 'confirmed', 'label' => __('Confirmed'), 'rank' => 1],
            ['key' => 'production', 'label' => __('Production'), 'rank' => 2],
            ['key' => 'delivered', 'label' => __('Delivered'), 'rank' => 3],
            ['key' => 'closed', 'label' => __('Closed'), 'rank' => 4],
        ];

        return array_map(function (array $step) use ($rank, $salesOrder) {
            if ($salesOrder->status === SalesOrderStatus::Cancelled) {
                return [...$step, 'state' => 'cancelled'];
            }

            if ($salesOrder->status === SalesOrderStatus::OnHold) {
                return [...$step, 'state' => $step['rank'] <= max(1, $rank) ? 'paused' : 'upcoming'];
            }

            if ($step['rank'] < $rank) {
                return [...$step, 'state' => 'complete'];
            }

            if ($step['rank'] === $rank) {
                return [...$step, 'state' => 'current'];
            }

            return [...$step, 'state' => 'upcoming'];
        }, $steps);
    }

    protected function statusRank(SalesOrderStatus $status): int
    {
        return match ($status) {
            SalesOrderStatus::Draft => 0,
            SalesOrderStatus::Confirmed,
            SalesOrderStatus::ReadyForProduction => 1,
            SalesOrderStatus::InProduction,
            SalesOrderStatus::Completed,
            SalesOrderStatus::ReadyForDispatch => 2,
            SalesOrderStatus::Delivered => 3,
            SalesOrderStatus::Closed => 4,
            default => 0,
        };
    }
}
