<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionQueueStatus;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\ProductionStage;
use App\Models\Production\WorkCenter;
use App\Services\Production\ProductionQueueWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductionQueueController extends Controller
{
    public function index(Request $request, ProductionQueueWorkspaceService $workspace): View
    {
        $this->authorize('viewWorkspace', ProductionQueue::class);

        return view('admin.production.queue.index', [
            'queues' => $workspace->paginatedIndex($request),
            'kpis' => $workspace->kpiCounts(),
            'workCenters' => WorkCenter::query()->forTenant()->orderBy('name')->get(['id', 'name']),
            'stages' => ProductionStage::query()->forTenant()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'operators' => $workspace->operatorOptions(),
            'filters' => [
                'status' => $request->query('status'),
                'work_center_id' => $request->query('work_center_id'),
                'operator_id' => $request->query('operator_id'),
                'stage_id' => $request->query('stage_id'),
                'date' => $request->query('date'),
                'search' => $request->query('search'),
            ],
            'workspace' => $workspace,
        ]);
    }

    public function store(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('create', [ProductionQueue::class, $jobCard]);

        $validated = $request->validate([
            'work_center_id' => [
                'required',
                Rule::exists('work_centers', 'id')
                    ->where('company_id', $jobCard->company_id)
                    ->where('branch_id', $jobCard->branch_id),
            ],
            'queue_position' => ['required', 'integer', 'min:1'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
        ]);

        $status = ! empty($validated['assigned_operator_id'])
            ? ProductionQueueStatus::Assigned
            : ProductionQueueStatus::Pending;

        ProductionQueue::query()->create([
            ...$validated,
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'status' => $status,
        ]);

        return back()->with('status', __('Added to production queue.'));
    }

    public function update(Request $request, ProductionJobCard $jobCard, ProductionQueue $queue): RedirectResponse
    {
        $this->authorize('update', $queue);
        abort_unless($queue->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'queue_position' => ['required', 'integer', 'min:1'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::enum(ProductionQueueStatus::class)],
        ]);

        $queue->update($validated);

        return back()->with('status', __('Queue entry updated.'));
    }

    public function destroy(ProductionJobCard $jobCard, ProductionQueue $queue): RedirectResponse
    {
        $this->authorize('delete', $queue);
        abort_unless($queue->production_job_card_id === $jobCard->id, 404);

        $queue->delete();

        return back()->with('status', __('Removed from queue.'));
    }
}
