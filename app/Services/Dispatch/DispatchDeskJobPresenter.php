<?php

namespace App\Services\Dispatch;

use App\Models\Production\ProductionJobCard;
use App\Services\Production\JobProductionControlService;
use Illuminate\Support\Collection;

class DispatchDeskJobPresenter
{
    public function __construct(
        protected JobProductionControlService $productionControl,
    ) {}

    /**
     * @param  Collection<int, ProductionJobCard>  $jobs
     * @return list<array<string, mixed>>
     */
    public function presentMany(Collection $jobs): array
    {
        return $jobs
            ->map(fn (ProductionJobCard $job) => $this->present($job))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function present(ProductionJobCard $job): array
    {
        $workflow = $this->productionControl->deliveryNoteWorkflow($job);

        return [
            'job' => $job,
            'workflow' => $workflow,
            'eligible_for_delivery_note' => $workflow['eligible'],
        ];
    }
}
