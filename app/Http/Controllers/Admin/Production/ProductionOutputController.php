<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Production\StoreProductionOutputRequest;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;
use App\Services\Production\ProductionCompletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductionOutputController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected ProductionCompletionService $completion,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ProductionOutput::class);

        $outputs = $this->scopeToTenant(
            ProductionOutput::query()
                ->with(['jobCard', 'finishedItem', 'finishedWarehouse', 'completedByUser', 'postedJournal'])
                ->latest('completed_at')
                ->latest('id')
        )->paginate(20);

        return view('admin.production.outputs.index', compact('outputs'));
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
