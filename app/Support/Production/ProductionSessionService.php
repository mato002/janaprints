<?php

namespace App\Support\Production;

use App\Enums\ProductionSessionWasteReason;
use App\Enums\ProductionWasteType;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\Production\ProductionSession;
use App\Models\Production\ProductionSessionMaterial;
use App\Support\ProductionMaterialConsumptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionSessionService
{
    public function __construct(
        protected MaterialRequirementsService $requirements,
        protected ProductionWastageService $wastage,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordSession(ProductionJobCard $jobCard, array $payload, int $operatorUserId): ProductionSession
    {
        return DB::transaction(function () use ($jobCard, $payload, $operatorUserId) {
            $session = ProductionSession::query()->create([
                'company_id' => $jobCard->company_id,
                'branch_id' => $jobCard->branch_id,
                'production_job_card_id' => $jobCard->id,
                'operator_user_id' => $operatorUserId,
                'started_at' => $payload['started_at'],
                'ended_at' => $payload['ended_at'] ?? null,
                'expected_quantity' => $payload['expected_quantity'] ?? 0,
                'produced_quantity' => $payload['produced_quantity'] ?? 0,
                'waste_quantity' => $payload['waste_quantity'] ?? 0,
                'waste_reason' => $payload['waste_reason'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            if (! empty($payload['materials']) && is_array($payload['materials'])) {
                $this->captureSessionMaterials($session, $jobCard, $payload['materials'], $operatorUserId);
            }

            return $session->fresh(['materials.inventoryItem']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function captureSessionMaterials(
        ProductionSession $session,
        ProductionJobCard $jobCard,
        array $lines,
        int $userId,
    ): void {
        foreach ($lines as $line) {
            $consumed = (float) ($line['consumed_quantity'] ?? 0);
            $waste = (float) ($line['waste_quantity'] ?? 0);
            $returned = (float) ($line['returned_quantity'] ?? 0);

            if ($consumed <= 0 && $waste <= 0 && $returned <= 0) {
                continue;
            }

            $itemId = (int) ($line['inventory_item_id'] ?? 0);
            $warehouseId = (int) ($line['warehouse_id'] ?? 0);
            $requirementId = isset($line['production_material_requirement_id'])
                ? (int) $line['production_material_requirement_id']
                : null;

            if ($itemId <= 0 || $warehouseId <= 0) {
                throw ValidationException::withMessages([
                    'materials' => __('Each material line requires an item and warehouse.'),
                ]);
            }

            ProductionSessionMaterial::query()->create([
                'production_session_id' => $session->id,
                'production_material_requirement_id' => $requirementId,
                'inventory_item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'consumed_quantity' => $consumed,
                'waste_quantity' => $waste,
                'returned_quantity' => $returned,
            ]);

            if ($consumed > 0) {
                if ($requirementId !== null) {
                    $requirement = ProductionMaterialRequirement::query()
                        ->where('production_job_card_id', $jobCard->id)
                        ->findOrFail($requirementId);

                    $this->requirements->consumeFromRequirement($requirement, $userId, $consumed);
                } else {
                    $item = InventoryItem::query()->findOrFail($itemId);
                    ProductionMaterialConsumptionService::consume(
                        $jobCard,
                        $item,
                        $warehouseId,
                        $consumed,
                        $userId,
                    );
                }
            }

            if ($waste > 0) {
                $this->wastage->recordWaste($jobCard, [
                    'inventory_item_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $waste,
                    'waste_type' => ProductionWasteType::Damage->value,
                    'notes' => __('Captured from production session :id', ['id' => $session->id]),
                ], $userId);
            }

            if ($returned > 0) {
                $this->wastage->recordReturn($jobCard, [
                    'inventory_item_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $returned,
                    'notes' => __('Captured from production session :id', ['id' => $session->id]),
                ], $userId);
            }
        }
    }

    /**
     * @return array{session_count: int, total_produced: float, total_waste: float, waste_by_reason: array<string, float>}
     */
    public function jobMetrics(ProductionJobCard $jobCard): array
    {
        $sessions = $jobCard->relationLoaded('productionSessions')
            ? $jobCard->productionSessions
            : $jobCard->productionSessions()->get();

        $wasteByReason = [];

        foreach ($sessions as $session) {
            if ($session->waste_quantity > 0 && $session->waste_reason) {
                $key = $session->waste_reason->value;
                $wasteByReason[$key] = ($wasteByReason[$key] ?? 0) + (float) $session->waste_quantity;
            }
        }

        return [
            'session_count' => $sessions->count(),
            'total_produced' => round((float) $sessions->sum('produced_quantity'), 3),
            'total_waste' => round((float) $sessions->sum('waste_quantity'), 3),
            'waste_by_reason' => $wasteByReason,
        ];
    }

    /**
     * @return list<ProductionSessionWasteReason>
     */
    public function wasteReasons(): array
    {
        return ProductionSessionWasteReason::cases();
    }
}
