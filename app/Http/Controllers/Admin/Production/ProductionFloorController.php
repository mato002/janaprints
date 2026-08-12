<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;
use App\Models\Production\ProductionQueue;
use App\Services\Assets\MachineJobAssignmentService;
use App\Services\Production\DepartmentCommandCenterService;
use App\Services\Production\ProductionFloorActionService;
use App\Services\Production\ProductionFloorService;
use App\Services\Production\ProductionJobCardIndexService;
use App\Support\Production\DepartmentQueueRegistry;
use App\Support\Production\ProductionDeskPersona;
use App\Support\Production\ProductionFloorDeskViews;
use App\Support\Production\ReturnsToProductionFloor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionFloorController extends Controller
{
    use ReturnsToProductionFloor;

    public function index(
        Request $request,
        ProductionFloorService $floor,
        ProductionJobCardIndexService $jobCardIndex,
        DepartmentCommandCenterService $commandCenter,
        DepartmentQueueRegistry $departments,
    ): View|RedirectResponse {
        $this->authorize('viewAny', ProductionJobCard::class);

        $persona = ProductionDeskPersona::resolve($request->user());
        $activeFloorView = ProductionFloorDeskViews::normalize($request->query('view'));
        $operatorMode = $request->user()?->prefersProductionOperatorMode() ?? false;

        if ($persona->prefersQueueLanding() && $activeFloorView !== ProductionFloorDeskViews::QUEUE) {
            return redirect()->to($persona->defaultFloorUrl());
        }

        if ($activeFloorView === ProductionFloorDeskViews::REGISTER) {
            $payload = $jobCardIndex->build($request);

            // When opening "New job card" from within the production floor register,
            // ensure the create form submission knows it should return back to the floor context.
            if (($payload['create_url'] ?? null) && ! $request->filled('from')) {
                $payload['create_url'] = route('admin.production.job-cards.create', [
                    'from' => 'production-floor',
                ]);
            }

            return view('admin.production.floor.index', array_merge($payload, [
                'activeFloorView' => $activeFloorView,
                'operatorMode' => $operatorMode,
                'deskPersona' => $persona,
                'embeddedInFloor' => true,
            ]));
        }

        if ($activeFloorView === ProductionFloorDeskViews::QUEUE) {
            $this->authorize('viewWorkspace', ProductionQueue::class);

            $department = $request->query('department');
            $department = is_string($department) && $department !== '' ? $department : null;

            if ($department !== null && ! $departments->isValidSlug($department)) {
                return redirect()->to(ProductionFloorDeskViews::queueIndexUrl());
            }

            if ($persona->isOperator() && $department === null && array_key_first($departments->availableDepartments()) !== null) {
                return redirect()->to($persona->defaultFloorUrl());
            }

            return view('admin.production.floor.index', array_merge($commandCenter->build($request, $department), [
                'activeFloorView' => $activeFloorView,
                'operatorMode' => $operatorMode,
                'deskPersona' => $persona,
                'embeddedInFloor' => true,
            ]));
        }

        if ($activeFloorView === ProductionFloorDeskViews::OUTPUTS) {
            $this->authorize('viewAny', ProductionOutput::class);

            $outputs = ProductionOutput::query()
                ->forTenant()
                ->with(['jobCard', 'finishedItem', 'finishedWarehouse', 'completedByUser', 'postedJournal'])
                ->latest('completed_at')
                ->latest('id')
                ->paginate(20);

            return view('admin.production.floor.index', [
                'activeFloorView' => $activeFloorView,
                'operatorMode' => $operatorMode,
                'deskPersona' => $persona,
                'embeddedInFloor' => true,
                'outputs' => $outputs,
            ]);
        }

        $payload = $floor->build($request);
        $payload['activeFloorView'] = ProductionFloorDeskViews::FLOOR;
        $payload['operatorMode'] = $operatorMode;
        $payload['deskPersona'] = $persona;

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
