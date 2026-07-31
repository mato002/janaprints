<?php

namespace App\Support\Procurement;

use App\Http\Controllers\Admin\Procurement\Concerns\ResolvesProcurementTenant;
use Illuminate\Http\Request;

class BuyDeskPageBuilder
{
    use ResolvesProcurementTenant;

    public function __construct(
        protected BuyDeskService $desk,
        protected BuyDeskWorkQueueService $workQueue,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request): array
    {
        $user = $request->user();
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $workQueue = $this->workQueue->present($request, $companyId, $branchId);

        return [
            'workQueue' => $workQueue,
            'fastActions' => $this->desk->fastActions($user),
            'pipelineStages' => $this->desk->pipelineStages($workQueue['counts'] ?? []),
            'receivingPipeline' => $this->desk->receivingPipeline($user),
            'queueItems' => $workQueue['items'] ?? [],
            'fullSupplyChainDeskUrl' => route('admin.workspaces.supply-chain', ['desk' => 1]),
        ];
    }
}
