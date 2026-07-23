<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Enums\QualityCheckResult;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Services\Assets\MachineJobAssignmentService;
use App\Services\Production\Job360WorkspaceService;
use App\Services\Production\ProductionFloorService;
use App\Services\Production\JobProductionControlService;
use App\Services\Production\ProductionJobCardIndexService;
use App\Enums\WorkflowRuleTrigger;
use App\Support\Governance\WorkflowRulesService;
use App\Support\Export\TabularExportWriter;
use App\Support\Production\ProductionJobCardEligibilityService;
use App\Support\Production\ProductionQueueService;
use App\Support\Production\ReturnsToProductionFloor;
use App\Support\Sales\ReturnsToSalesDesk;
use App\Support\ProductionJobCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductionJobCardController extends Controller
{
    use HandlesModalFormResponses, ReturnsToProductionFloor, ReturnsToSalesDesk, ScopesToTenant;

    public function index(Request $request, ProductionJobCardIndexService $index): View
    {
        $this->authorize('viewAny', ProductionJobCard::class);

        return view('admin.production.job-cards.index', $index->build($request));
    }

    public function export(Request $request, ProductionJobCardIndexService $index, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', ProductionJobCard::class);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $rows = $index->exportIndex($request)->map(
            fn (ProductionJobCard $jobCard) => $index->exportRow($jobCard),
        );

        return $writer->download(
            $format,
            'job-cards-register-'.now()->format('Y-m-d'),
            $index->exportHeaders(),
            $rows,
            __('Job Cards'),
        );
    }

    public function create(Request $request, ProductionJobCardEligibilityService $eligibility): View
    {
        $this->authorize('create', ProductionJobCard::class);

        $eligibleOrders = $eligibility->eligibleSalesOrders();
        $resolution = $eligibility->resolutionContext();
        $fromProductionFloor = $this->wantsProductionFloorReturn($request);

        return view('admin.production.job-cards.create', [
            'eligibleOrders' => $eligibleOrders,
            'eligibilitySummary' => $resolution['summary'],
            'resolutionContext' => $resolution,
            'productionTypes' => ProductionType::cases(),
            'priorities' => ProductionPriority::cases(),
            'salesOrderCreateUrl' => Route::has('admin.sales-orders.create')
                ? route('admin.sales-orders.create', array_filter([
                    'tab' => 'direct',
                    'from' => $fromProductionFloor ? 'production-floor' : null,
                ]))
                : null,
            'fromProductionFloor' => $fromProductionFloor,
            'preselectedSalesOrderId' => $request->integer('sales_order_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', ProductionJobCard::class);

        $validated = $request->validate([
            'sales_order_id' => ['required', 'exists:sales_orders,id'],
            'production_type' => ['required', Rule::enum(ProductionType::class)],
            'priority' => ['required', Rule::enum(ProductionPriority::class)],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
        ]);

        $salesOrder = SalesOrder::query()->forTenant()->findOrFail($validated['sales_order_id']);

        try {
            $jobCard = ProductionJobCardService::createFromSalesOrder(
                $salesOrder,
                (int) auth()->id(),
                $validated,
            );
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return $this->modalOrRedirect(
            __('Job card created.'),
            $this->wantsProductionFloorReturn($request)
                ? redirect()->route('admin.production.floor', ['job' => $jobCard->public_id])
                : redirect()->route('admin.production.job-cards.show', $jobCard),
        );
    }

    public function show(
        Request $request,
        ProductionJobCard $jobCard,
        Job360WorkspaceService $workspace,
        ProductionFloorService $floor,
    ): View {
        $this->authorize('view', $jobCard);

        if ($this->wantsSalesDeskReturn($request) || $this->wantsProductionFloorReturn($request)) {
            return view('admin.production.floor.job-modal', [
                'jobCard' => $jobCard,
                'panel' => $floor->panel($jobCard, true),
            ]);
        }

        $payload = $workspace->build(
            $jobCard,
            $request->query('tab'),
            [
                'timeline_filter' => $request->query('timeline_filter'),
                'timeline_search' => $request->query('timeline_search'),
                'timeline_page' => $request->query('timeline_page'),
            ],
        );

        return view('admin.production.job-cards.show', [
            'workspace' => $payload,
            'jobCard' => $payload['jobCard'],
        ]);
    }

    public function edit(ProductionJobCard $jobCard): View
    {
        $this->authorize('update', $jobCard);

        return view('admin.production.job-cards.edit', [
            'jobCard' => $jobCard,
            'productionTypes' => ProductionType::cases(),
            'priorities' => ProductionPriority::cases(),
        ]);
    }

    public function update(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('update', $jobCard);

        $jobCard->update($request->validate([
            'production_type' => ['required', Rule::enum(ProductionType::class)],
            'priority' => ['required', Rule::enum(ProductionPriority::class)],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date'],
        ]));

        return redirect()
            ->route('admin.production.job-cards.show', $jobCard)
            ->with('status', __('Job card updated.'));
    }

    public function destroy(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('delete', $jobCard);

        $jobCard->delete();

        return redirect()
            ->route('admin.production.job-cards.index')
            ->with('status', __('Job card deleted.'));
    }

    public function queue(Request $request, ProductionJobCard $jobCard, ProductionQueueService $queues): RedirectResponse
    {
        $this->authorize('schedule', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Queued), 403);

        if ($queues->hasActiveQueue($jobCard)) {
            if ($jobCard->status !== ProductionJobCardStatus::Queued) {
                $jobCard->transitionTo(ProductionJobCardStatus::Queued);
            }

            return $this->redirectAfterProductionFloorAction($jobCard, __('Job card queued.'));
        }

        $validated = $request->validate($queues->queueValidationRules($jobCard));

        $queues->enqueue(
            $jobCard,
            (int) $validated['work_center_id'],
            isset($validated['queue_position']) ? (int) $validated['queue_position'] : null,
            isset($validated['assigned_operator_id']) ? (int) $validated['assigned_operator_id'] : null,
        );

        return $this->redirectAfterProductionFloorAction($jobCard, __('Job card queued.'));
    }

    public function start(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('start', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction), 403);

        $execution = app(\App\Services\Production\JobExecutionStateService::class);
        if (! $execution->isReadyToStart($jobCard)) {
            throw ValidationException::withMessages([
                'status' => __('Assign an operator (and machine, when required) before starting work.'),
            ]);
        }

        $jobCard->update([
            'status' => ProductionJobCardStatus::InProduction,
            'actual_start_date' => $jobCard->actual_start_date ?? now(),
        ]);

        if ($jobCard->salesOrder && $jobCard->salesOrder->status === SalesOrderStatus::Confirmed) {
            $jobCard->salesOrder->transitionTo(SalesOrderStatus::InProduction);
        }

        return $this->redirectAfterProductionFloorAction($jobCard, __('Production started.'));
    }

    public function sendToQc(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('complete', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::QualityCheck), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::QualityCheck);
        app(\App\Support\Production\ProductQcChecklistService::class)->snapshotForJobCard($jobCard);

        return $this->redirectAfterProductionFloorAction($jobCard, __('Job sent to quality check.'));
    }

    public function markCompleted(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('complete', $jobCard);

        if ($jobCard->status === ProductionJobCardStatus::QualityCheck) {
            $qcRequired = app(\App\Support\Production\ProductionQcSettings::class)
                ->qcRequired($jobCard->company_id, $jobCard->branch_id);

            if ($qcRequired) {
                abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed), 403);
            } elseif ($jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed)) {
                $jobCard->transitionTo(ProductionJobCardStatus::Completed);

                return $this->redirectAfterProductionFloorAction($jobCard, __('Production complete — post finished goods to release for dispatch.'));
            }
        }

        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed), 403);
        $jobCard->update([
            'status' => ProductionJobCardStatus::Completed,
            'actual_end_date' => now(),
        ]);
        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Completed, $jobCard->fresh(), auth()->user());

        return $this->redirectAfterProductionFloorAction($jobCard, __('Job card completed.'));
    }

    public function readyForDispatch(ProductionJobCard $jobCard, JobProductionControlService $controls): RedirectResponse
    {
        $this->authorize('complete', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch), 403);

        $eligibility = $controls->dispatchEligibility($jobCard);
        abort_unless($eligibility['eligible'], 403, implode(' ', $eligibility['blockers']));

        $jobCard->transitionTo(ProductionJobCardStatus::ReadyForDispatch);

        if ($jobCard->salesOrder && $jobCard->salesOrder->status === SalesOrderStatus::InProduction) {
            $jobCard->salesOrder->transitionTo(SalesOrderStatus::Completed);
        }

        return $this->redirectAfterProductionFloorAction($jobCard, __('Job ready for dispatch.'));
    }

    public function hold(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('transition', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::OnHold);

        return $this->redirectAfterProductionFloorAction($jobCard, __('Job card on hold.'));
    }

    public function pause(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('transition', $jobCard);
        abort_unless($jobCard->status === ProductionJobCardStatus::InProduction, 403);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::OnHold);

        return $this->redirectAfterProductionFloorAction($jobCard, __('Production paused.'));
    }

    public function resume(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('transition', $jobCard);
        abort_unless($jobCard->status === ProductionJobCardStatus::OnHold, 403);

        $target = $jobCard->actual_start_date
            ? ProductionJobCardStatus::InProduction
            : ProductionJobCardStatus::Queued;

        abort_unless($jobCard->status->canTransitionTo($target), 403);
        $jobCard->transitionTo($target);

        return $this->redirectAfterProductionFloorAction($jobCard, __('Production resumed.'));
    }

    public function cancel(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('transition', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Cancelled), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::Cancelled);

        return $this->redirectAfterProductionFloorAction($jobCard, __('Job card cancelled.'));
    }

    public function schedule(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('schedule', $jobCard);

        $jobCard->update($request->validate([
            'planned_start_date' => ['required', 'date'],
            'planned_end_date' => ['required', 'date', 'after_or_equal:planned_start_date'],
        ]));

        return $this->redirectAfterProductionFloorAction($jobCard, __('Schedule updated.'));
    }

    public function assignMachine(
        Request $request,
        ProductionJobCard $jobCard,
        MachineJobAssignmentService $assignments,
    ): RedirectResponse {
        $this->authorize('update', $jobCard);
        abort_unless($request->user()?->can('machines.assign'), 403);

        $validated = $request->validate([
            'assigned_machine_asset_id' => ['required', 'exists:fixed_assets,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $assignments->assignFromHttpRequest(
            $jobCard,
            (int) $validated['assigned_machine_asset_id'],
            (int) auth()->id(),
            $validated['notes'] ?? null,
        );

        return $this->redirectAfterProductionFloorAction($jobCard, __('Machine assigned to job card.'));
    }

    public function assignOperator(
        Request $request,
        ProductionJobCard $jobCard,
        ProductionQueueService $queues,
    ): RedirectResponse {
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

        if ($queue === null) {
            throw ValidationException::withMessages([
                'assigned_operator_id' => __('No active queue entry found for this job.'),
            ]);
        }

        abort_unless($queue->production_job_card_id === $jobCard->id, 404);

        $queues->updateEntry($queue, [
            'assigned_operator_id' => (int) $validated['assigned_operator_id'],
        ]);

        if ($jobCard->status === ProductionJobCardStatus::Draft && $queues->hasActiveQueue($jobCard)) {
            $jobCard->update(['status' => ProductionJobCardStatus::Queued]);
        }

        return $this->redirectAfterProductionFloorAction($jobCard, __('Operator assigned. Job is ready to start when machine requirements are met.'));
    }
}
