<?php

namespace App\Support\Production;

use App\Enums\ProductionPriority;
use App\Enums\ProductionQueueStatus;
use App\Models\Production\ProductionQueue;
use App\Models\Production\WorkCenter;
use Illuminate\Database\Eloquent\Builder;

class ProductionQueueOrderingService
{
    /**
     * Apply priority → due date → created date ordering for queue listings.
     */
    public function applyPriorityOrdering(Builder $query, string $jobCardTable = 'production_job_cards'): Builder
    {
        return $query
            ->join($jobCardTable, "{$jobCardTable}.id", '=', 'production_queues.production_job_card_id')
            ->orderByRaw($this->priorityOrderSql("{$jobCardTable}.priority"))
            ->orderBy("{$jobCardTable}.required_date")
            ->orderBy("{$jobCardTable}.planned_end_date")
            ->orderBy("{$jobCardTable}.created_at")
            ->orderBy('production_queues.queue_position')
            ->orderBy('production_queues.id')
            ->select('production_queues.*');
    }

    public function priorityOrderSql(string $column = 'priority'): string
    {
        $urgent = ProductionPriority::Urgent->value;
        $high = ProductionPriority::High->value;
        $normal = ProductionPriority::Normal->value;
        $low = ProductionPriority::Low->value;

        return "CASE {$column}"
            ." WHEN '{$urgent}' THEN 1"
            ." WHEN '{$high}' THEN 2"
            ." WHEN '{$normal}' THEN 3"
            ." WHEN '{$low}' THEN 4"
            .' ELSE 5 END ASC';
    }

    public function reorderWorkCenter(WorkCenter $workCenter): void
    {
        $entries = ProductionQueue::query()
            ->where('work_center_id', $workCenter->id)
            ->whereIn('status', array_map(fn (ProductionQueueStatus $s) => $s->value, ProductionQueueStatus::activeStatuses()))
            ->with('jobCard:id,priority,required_date,planned_end_date,created_at')
            ->get();

        $sorted = $entries->sort(function (ProductionQueue $a, ProductionQueue $b) {
            $priorityCompare = $this->priorityRank($a->jobCard?->priority)
                <=> $this->priorityRank($b->jobCard?->priority);

            if ($priorityCompare !== 0) {
                return $priorityCompare;
            }

            $dueA = $a->jobCard?->required_date ?? $a->jobCard?->planned_end_date;
            $dueB = $b->jobCard?->required_date ?? $b->jobCard?->planned_end_date;

            if ($dueA && $dueB && $dueA->ne($dueB)) {
                return $dueA->lessThan($dueB) ? -1 : 1;
            }

            if ($dueA && ! $dueB) {
                return -1;
            }

            if (! $dueA && $dueB) {
                return 1;
            }

            $createdA = $a->jobCard?->created_at;
            $createdB = $b->jobCard?->created_at;

            if ($createdA && $createdB && ! $createdA->equalTo($createdB)) {
                return $createdA->lessThan($createdB) ? -1 : 1;
            }

            return $a->id <=> $b->id;
        })->values();

        foreach ($sorted as $index => $entry) {
            $entry->update(['queue_position' => $index + 1]);
        }
    }

    protected function priorityRank(?ProductionPriority $priority): int
    {
        return match ($priority) {
            ProductionPriority::Urgent => 1,
            ProductionPriority::High => 2,
            ProductionPriority::Normal => 3,
            ProductionPriority::Low => 4,
            default => 5,
        };
    }
}
