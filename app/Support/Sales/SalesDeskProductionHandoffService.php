<?php

namespace App\Support\Sales;

use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;

class SalesDeskProductionHandoffService
{
    /**
     * @return array<string, mixed>|null
     */
    public function present(?ProductionJobCard $jobCard): ?array
    {
        if (! $jobCard) {
            return null;
        }

        $jobCard->loadMissing([
            'queues.workCenter:id,name,code',
            'productionSpecification:id,production_job_card_id,product_description',
        ]);

        $queue = $jobCard->queues
            ->sortBy('queue_position')
            ->first();

        $department = $this->departmentSlug($jobCard->production_type?->value);

        return [
            'job_card_number' => $jobCard->job_card_number,
            'production_type' => $jobCard->production_type?->value
                ? str_replace('_', ' ', ucfirst($jobCard->production_type->value))
                : null,
            'department' => $department,
            'department_label' => $department ? ucfirst($department) : null,
            'product' => $jobCard->productionSpecification?->product_description,
            'queue_status' => $queue?->status
                ? str_replace('_', ' ', ucfirst($queue->status->value))
                : __('Not queued'),
            'work_center' => $queue?->workCenter?->name,
            'job_status' => str_replace('_', ' ', ucfirst($jobCard->status->value)),
            'department_queue_url' => $department && auth()->user()?->can('production.queue.view')
                ? route('admin.production.queue.department', $department)
                : null,
        ];
    }

    protected function departmentSlug(?string $productionType): ?string
    {
        return match ($productionType) {
            'digital' => 'digital',
            'offset' => 'offset',
            'large_format' => 'large_format',
            'finishing' => 'finishing',
            default => null,
        };
    }
}
