<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Production\StoreProductionOutputRequest;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;
use App\Services\Production\ProductionCompletionService;
use App\Support\Production\ProductionFloorDeskViews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductionOutputController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected ProductionCompletionService $completion,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', ProductionOutput::class);

        return redirect()->to(ProductionFloorDeskViews::outputsIndexUrl($request->query->all()));
    }

    public function store(
        StoreProductionOutputRequest $request,
        ProductionJobCard $jobCard,
    ): RedirectResponse {
        $this->authorize('post', ProductionOutput::class);

        $allowManualCost = $request->user()?->can('production.outputs.manual-cost') ?? false;

        $output = $this->completion->post(
            $jobCard,
            $request->validated(),
            (int) $request->user()->id,
            $allowManualCost,
        );

        return redirect()
            ->route('admin.production.job-cards.show', [
                'jobCard' => $jobCard,
                'tab' => 'outputs',
            ])
            ->with('status', __('Finished goods output :reference posted.', [
                'reference' => $output->jobCard?->job_card_number ?? $output->id,
            ]));
    }
}
