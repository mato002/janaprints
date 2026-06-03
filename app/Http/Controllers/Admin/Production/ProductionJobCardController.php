<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Enums\QualityCheckResult;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\ProductionJobCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductionJobCardController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        $this->authorize('viewAny', ProductionJobCard::class);

        $jobCards = $this->scopeToTenant(
            ProductionJobCard::query()->with(['customer', 'salesOrder', 'creator'])
        )->latest()->paginate(15);

        return view('admin.production.job-cards.index', compact('jobCards'));
    }

    public function create(): View
    {
        $this->authorize('create', ProductionJobCard::class);

        $eligibleOrders = SalesOrder::query()
            ->forTenant()
            ->where('status', SalesOrderStatus::Confirmed)
            ->whereDoesntHave('jobCard')
            ->with(['customer', 'artworkRequest'])
            ->orderByDesc('order_date')
            ->get();

        return view('admin.production.job-cards.create', [
            'eligibleOrders' => $eligibleOrders,
            'productionTypes' => ProductionType::cases(),
            'priorities' => ProductionPriority::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        return redirect()
            ->route('admin.production.job-cards.show', $jobCard)
            ->with('status', __('Job card created.'));
    }

    public function show(ProductionJobCard $jobCard): View
    {
        $this->authorize('view', $jobCard);

        $jobCard->load([
            'customer', 'salesOrder', 'quotation', 'artworkRequest', 'creator',
            'queues.workCenter', 'queues.assignedOperator',
            'operations.workCenter', 'operations.stage', 'operations.assignedEmployee',
            'qualityChecks.checker',
            'materialConsumptions.inventoryItem', 'materialConsumptions.warehouse',
        ]);

        return view('admin.production.job-cards.show', compact('jobCard'));
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

        return back()->with('status', __('Job sent to quality check.'));
    }

    public function markCompleted(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('complete', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::Completed), 403);
        $jobCard->update([
            'status' => ProductionJobCardStatus::Completed,
            'actual_end_date' => now(),
        ]);

        return back()->with('status', __('Job card completed.'));
    }

    public function readyForDispatch(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('complete', $jobCard);
        abort_unless($jobCard->status->canTransitionTo(ProductionJobCardStatus::ReadyForDispatch), 403);
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
}
