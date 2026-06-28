<?php

namespace App\Support\Production;

use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductQcChecklist;
use App\Models\Production\ProductQcChecklistLine;
use App\Models\Production\JobCardQcSnapshot;
use App\Models\Production\ProductionJobCard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductQcChecklistService
{
    /** @var list<string> */
    public const DEFAULT_LINES = [
        'Correct Quantity',
        'Correct Artwork',
        'Correct Serial Range',
        'Correct Numbering',
        'Correct Binding',
        'Correct Finishing',
        'Correct Packaging',
    ];

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function syncFromCatalogItem(InventoryItem $item, array $lines, int $userId): ?ProductQcChecklist
    {
        $lines = collect($lines)
            ->filter(fn (array $line) => filled($line['label'] ?? null))
            ->values()
            ->all();

        if ($lines === []) {
            ProductQcChecklist::query()
                ->where('company_id', $item->company_id)
                ->where('branch_id', $item->branch_id)
                ->where('finished_item_id', $item->id)
                ->delete();

            return null;
        }

        return DB::transaction(function () use ($item, $lines, $userId) {
            $checklist = ProductQcChecklist::query()->updateOrCreate(
                [
                    'company_id' => $item->company_id,
                    'branch_id' => $item->branch_id,
                    'finished_item_id' => $item->id,
                ],
                ['is_active' => true, 'created_by' => $userId],
            );

            $checklist->lines()->delete();

            foreach ($lines as $index => $line) {
                $checklist->lines()->create([
                    'label' => (string) $line['label'],
                    'sort_order' => $line['sort_order'] ?? $index,
                    'is_active' => $line['is_active'] ?? true,
                ]);
            }

            return $checklist->fresh('lines');
        });
    }

    public function findActiveForFinishedItem(int $companyId, int $branchId, int $finishedItemId): ?ProductQcChecklist
    {
        return ProductQcChecklist::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('finished_item_id', $finishedItemId)
            ->where('is_active', true)
            ->with(['lines' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->first();
    }

    public function snapshotForJobCard(ProductionJobCard $jobCard): JobCardQcSnapshot
    {
        $existing = JobCardQcSnapshot::query()
            ->where('production_job_card_id', $jobCard->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $finishedItemId = $this->resolveFinishedItemId($jobCard);
        $checklist = $finishedItemId
            ? $this->findActiveForFinishedItem($jobCard->company_id, $jobCard->branch_id, $finishedItemId)
            : null;

        $items = $checklist?->lines->isNotEmpty()
            ? $checklist->lines->map(fn (ProductQcChecklistLine $line) => [
                'line_id' => $line->id,
                'label' => $line->label,
                'passed' => null,
            ])->values()->all()
            : collect(self::DEFAULT_LINES)->map(fn (string $label, int $i) => [
                'line_id' => null,
                'label' => $label,
                'passed' => null,
            ])->values()->all();

        return JobCardQcSnapshot::query()->create([
            'production_job_card_id' => $jobCard->id,
            'checklist_items' => $items,
            'snapshotted_at' => now(),
        ]);
    }

    /**
     * @param  list<array{line_id: int|null, label: string, passed: bool}>  $answers
     * @return list<array<string, mixed>>
     */
    public function mergeChecklistAnswers(JobCardQcSnapshot $snapshot, array $answers): array
    {
        $indexed = collect($answers)->keyBy(fn ($row, $key) => $row['line_id'] ?? $key);

        return collect($snapshot->checklist_items)->map(function (array $item, int $index) use ($indexed) {
            $key = $item['line_id'] ?? $index;
            $answer = $indexed->get($key);

            return [
                ...$item,
                'passed' => isset($answer['passed']) ? (bool) $answer['passed'] : ($item['passed'] ?? null),
            ];
        })->all();
    }

    protected function resolveFinishedItemId(ProductionJobCard $jobCard): ?int
    {
        if ($jobCard->inventory_item_id) {
            return (int) $jobCard->inventory_item_id;
        }

        $orderItem = $jobCard->salesOrder?->items?->firstWhere('inventory_item_id', '!=', null);

        return $orderItem?->inventory_item_id ? (int) $orderItem->inventory_item_id : null;
    }

    /**
     * @return Collection<int, array{label: string, sort_order: int, is_active: bool}>
     */
    public function defaultLinePayload(): Collection
    {
        return collect(self::DEFAULT_LINES)->map(fn (string $label, int $i) => [
            'label' => $label,
            'sort_order' => $i,
            'is_active' => true,
        ]);
    }
}
