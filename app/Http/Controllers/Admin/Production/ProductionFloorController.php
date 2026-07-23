<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Models\Production\ProductionJobCard;
use App\Services\Assets\MachineJobAssignmentService;
use App\Services\Production\ProductionFloorActionService;
use App\Services\Production\ProductionFloorService;
use App\Support\Production\ReturnsToProductionFloor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionFloorController extends Controller
{
    use ReturnsToProductionFloor;

    public function index(Request $request, ProductionFloorService $floor): View
    {
        $this->authorize('viewAny', ProductionJobCard::class);

        $payload = $floor->build($request);
        $payload['operatorMode'] = $request->user()?->prefersProductionOperatorMode() ?? false;

        return view('admin.production.floor.index', $payload);
    }

    public function panel(Request $request, ProductionJobCard $jobCard, ProductionFloorService $floor): JsonResponse
    {
        $this->authorize('view', $jobCard);

        $operatorMode = $request->user()?->prefersProductionOperatorMode() ?? false;

        return response()->json($floor->panel($jobCard, $operatorMode));
    }

    public function assignMachine(
        Request $request,
        ProductionJobCard $jobCard,
        MachineJobAssignmentService $assignments,
    ): RedirectResponse|JsonResponse {
        $this->authorize('update', $jobCard);
        abort_unless($request->user()?->can('machines.assign'), 403);

        $validated = $request->validate([
            'assigned_machine_asset_id' => ['nullable', 'exists:fixed_assets,id'],
        ]);

        $jobCard = $assignments->assignFromHttpRequest(
            $jobCard,
            ! empty($validated['assigned_machine_asset_id']) ? (int) $validated['assigned_machine_asset_id'] : null,
            (int) $request->user()->id,
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => __('Machine assignment updated.'),
                'machine_id' => $jobCard->assigned_machine_asset_id,
                'machine_name' => $jobCard->assignedMachine?->asset_name,
            ]);
        }

        $params = array_merge(
            $request->only(['search', 'stage', 'machine_id', 'vendor_id', 'priority', 'overdue', 'desk']),
            $this->wantsProductionFloorReturn($request) ? ['job' => $jobCard->public_id] : [],
        );

        return redirect()
            ->route('admin.production.floor', $params)
            ->with('status', __('Machine assignment updated.'));
    }

    public function assignOperator(
        Request $request,
        ProductionJobCard $jobCard,
        \App\Support\Production\ProductionQueueService $queues,
    ): RedirectResponse|JsonResponse {
        abort_unless(
            $request->user()?->can('schedule', $jobCard) || $request->user()?->can('update', $jobCard),
            403,
        );

        $validated = $request->validate([
            'production_queue_id' => ['nullable', 'exists:production_queues,id'],
            'assigned_operator_id' => ['required', 'exists:users,id'],
        ]);

        $queue = null;
        if (! empty($validated['production_queue_id'])) {
            $queue = $jobCard->queues()->whereKey($validated['production_queue_id'])->first();
        }

        $queue ??= app(\App\Support\Production\RouteStepQueueService::class)
            ->currentQueueContext($jobCard)['current'] ?? null;

        abort_unless($queue && $queue->production_job_card_id === $jobCard->id, 404);

        $queues->updateEntry($queue, [
            'assigned_operator_id' => (int) $validated['assigned_operator_id'],
        ]);

        if ($jobCard->status === \App\Enums\ProductionJobCardStatus::Draft && $queues->hasActiveQueue($jobCard)) {
            $jobCard->update(['status' => \App\Enums\ProductionJobCardStatus::Queued]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => __('Operator assigned.'),
            ]);
        }

        return $this->redirectAfterProductionFloorAction($jobCard, __('Operator assigned.'));
    }

    public function quickPassQc(Request $request, ProductionJobCard $jobCard, ProductionFloorActionService $actions): RedirectResponse
    {
        $actions->quickPassQc($jobCard, $request->user());

        return $this->redirectAfterProductionFloorAction($jobCard, __('Quality check passed.'));
    }
}
