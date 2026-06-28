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
use App\Services\Production\JobProductionControlService;
use App\Services\Production\ProductionJobCardIndexService;
use App\Enums\WorkflowRuleTrigger;
use App\Support\Governance\WorkflowRulesService;
use App\Support\Export\TabularExportWriter;
use App\Support\Production\ProductionJobCardEligibilityService;
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
    use HandlesModalFormResponses, ScopesToTenant;

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

    public function create(ProductionJobCardEligibilityService $eligibility): View
    {
        $this->authorize('create', ProductionJobCard::class);

        $eligibleOrders = $eligibility->eligibleSalesOrders();

        return view('admin.production.job-cards.create', [
            'eligibleOrders' => $eligibleOrders,
            'productionTypes' => ProductionType::cases(),
            'priorities' => ProductionPriority::cases(),
            'salesOrdersUrl' => Route::has('admin.sales-orders.dashboard')
                ? route('admin.sales-orders.dashboard')
                : null,
            'salesOrderCreateUrl' => Route::has('admin.sales-orders.create')
                ? route('admin.sales-orders.create')
                : null,
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
            redirect()->route('admin.production.floor', ['job' => $jobCard->id]),
        );
    }

    public function show(Request $request, ProductionJobCard $jobCard, Job360WorkspaceService $workspace): View
    {
        $this->authorize('view', $jobCard);

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

    public function queue(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('schedule', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Queued), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::Queued);

        return back()->with('status', __('Job card queued.'));
    }

    public function start(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('start', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::InProduction), 403);

        $jobCard->update([
            'status' => ProductionJobCardStatus::InProduction,
            'actual_start_date' => $jobCard->actual_start_date ?? now(),
        ]);

        if ($jobCard->salesOrder && $jobCard->salesOrder->status === SalesOrderStatus::Confirmed) {
            $jobCard->salesOrder->transitionTo(SalesOrderStatus::InProduction);
        }

        return back()->with('status', __('Production started.'));
    }

    public function sendToQc(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('complete', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::QualityCheck), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::QualityCheck);
        app(\App\Support\Production\ProductQcChecklistService::class)->snapshotForJobCard($jobCard);

        return back()->with('status', __('Job sent to quality check.'));
    }

    public function markCompleted(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('complete', $jobCard);

        if ($jobCard->status === ProductionJobCardStatus::QualityCheck) {
            $qcRequired = app(\App\Support\Production\ProductionQcSettings::class)
                ->qcRequired($jobCard->company_id, $jobCard->branch_id);

            if ($qcRequired) {
                abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed), 403);
            } elseif ($jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch)) {
                $jobCard->transitionTo(ProductionJobCardStatus::ReadyForDispatch);

                return back()->with('status', __('Job ready for dispatch.'));
            }
        }

        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed), 403);
        $jobCard->update([
            'status' => ProductionJobCardStatus::Completed,
            'actual_end_date' => now(),
        ]);
        app(WorkflowRulesService::class)->dispatch(WorkflowRuleTrigger::Completed, $jobCard->fresh(), auth()->user());

        return back()->with('status', __('Job card completed.'));
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

        return back()->with('status', __('Job ready for dispatch.'));
    }

    public function hold(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('transition', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::OnHold);

        return back()->with('status', __('Job card on hold.'));
    }

    public function cancel(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('transition', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Cancelled), 403);
        $jobCard->transitionTo(ProductionJobCardStatus::Cancelled);

        return back()->with('status', __('Job card cancelled.'));
    }

    public function schedule(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('schedule', $jobCard);

        $jobCard->update($request->validate([
            'planned_start_date' => ['required', 'date'],
            'planned_end_date' => ['required', 'date', 'after_or_equal:planned_start_date'],
        ]));

        return back()->with('status', __('Schedule updated.'));
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

        $machine = FixedAsset::query()
            ->forTenant()
            ->whereHas('machineProfile')
            ->findOrFail($validated['assigned_machine_asset_id']);

        $assignments->assignToJob(
            $jobCard,
            $machine,
            (int) auth()->id(),
            $validated['notes'] ?? null,
        );

        return back()->with('status', __('Machine assigned to job card.'));
    }
}
