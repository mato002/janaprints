<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\JobCardRouteStepStatus;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionSessionWasteReason;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Production\JobCardRouteStep;
use App\Models\Production\ProductionJobCard;
use App\Models\Procurement\Vendor;
use App\Support\Production\JobCardOutsourceService;
use App\Support\Production\ProductionRouteService;
use App\Support\Production\ProductionSessionService;
use App\Support\Production\SerialNumberGovernanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductionGovernanceController extends Controller
{
    use HandlesModalFormResponses, ScopesToTenant;

    public function updateRouteStep(
        Request $request,
        ProductionJobCard $jobCard,
        JobCardRouteStep $step,
        ProductionRouteService $routes,
    ): RedirectResponse|Response {
        $this->authorize('start', $jobCard);
        abort_unless($step->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(JobCardRouteStepStatus::class)],
        ]);

        $routes->updateStepStatus(
            $step,
            JobCardRouteStepStatus::from($validated['status']),
            (int) auth()->id(),
        );

        return $this->modalOrRedirect(
            __('Route step updated.'),
            redirect()->route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'route']),
        );
    }

    public function confirmSerials(
        Request $request,
        ProductionJobCard $jobCard,
        SerialNumberGovernanceService $serials,
    ): RedirectResponse|Response {
        $this->authorize('complete', $jobCard);

        $allocation = $jobCard->serialAllocation;
        abort_unless($allocation, 404);

        $validated = $request->validate([
            'produced_end' => ['required', 'integer', 'min:'.$allocation->serial_start, 'max:'.$allocation->serial_end],
            'spoiled_start' => ['nullable', 'integer'],
            'spoiled_end' => ['nullable', 'integer', 'required_with:spoiled_start'],
        ]);

        $serials->confirmProduction($allocation, $validated, (int) auth()->id());

        return $this->modalOrRedirect(
            __('Serial production confirmed.'),
            redirect()->route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'serials']),
        );
    }

    public function storeSession(
        Request $request,
        ProductionJobCard $jobCard,
        ProductionSessionService $sessions,
    ): RedirectResponse|Response {
        $this->authorize('start', $jobCard);

        $validated = $request->validate([
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'expected_quantity' => ['required', 'numeric', 'min:0'],
            'produced_quantity' => ['required', 'numeric', 'min:0'],
            'waste_quantity' => ['nullable', 'numeric', 'min:0'],
            'waste_reason' => ['nullable', Rule::enum(ProductionSessionWasteReason::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'materials' => ['nullable', 'array'],
            'materials.*.production_material_requirement_id' => ['nullable', 'integer'],
            'materials.*.inventory_item_id' => ['required_with:materials', 'integer'],
            'materials.*.warehouse_id' => ['required_with:materials', 'integer'],
            'materials.*.consumed_quantity' => ['nullable', 'numeric', 'min:0'],
            'materials.*.waste_quantity' => ['nullable', 'numeric', 'min:0'],
            'materials.*.returned_quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sessions->recordSession($jobCard, $validated, (int) auth()->id());

        return $this->modalOrRedirect(
            __('Production session recorded.'),
            redirect()->route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'sessions']),
        );
    }

    public function outsource(
        Request $request,
        ProductionJobCard $jobCard,
        JobCardOutsourceService $outsource,
    ): RedirectResponse|Response {
        $this->authorize('update', $jobCard);

        $validated = $request->validate([
            'outsource_vendor_id' => ['required', 'exists:vendors,id'],
            'outsource_issue_date' => ['required', 'date'],
            'outsource_expected_return' => ['nullable', 'date', 'after_or_equal:outsource_issue_date'],
            'outsource_quoted_cost' => ['nullable', 'numeric', 'min:0'],
            'outsource_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $outsource->outsource($jobCard, $validated);

        return $this->modalOrRedirect(
            __('Production outsourced.'),
            redirect()->route('admin.production.job-cards.show', $jobCard),
        );
    }

    public function markReturned(
        Request $request,
        ProductionJobCard $jobCard,
        JobCardOutsourceService $outsource,
    ): RedirectResponse|Response {
        $this->authorize('update', $jobCard);

        $validated = $request->validate([
            'outsource_actual_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $outsource->markReturned(
            $jobCard,
            isset($validated['outsource_actual_cost']) ? (float) $validated['outsource_actual_cost'] : null,
        );

        return $this->modalOrRedirect(
            __('Outsourced production marked as returned.'),
            redirect()->route('admin.production.job-cards.show', $jobCard),
        );
    }

    public function floorDisplay(ProductionJobCard $jobCard, ProductionRouteService $routes): View
    {
        $this->authorize('view', $jobCard);

        $jobCard->load([
            'customer:id,company_name',
            'inventoryItem:id,item_name,sku',
            'customerArtwork:id,artwork_name,version_number,file_path,mime_type,customer_id',
            'serialAllocation',
            'routeSteps' => fn ($q) => $q->orderBy('sequence'),
        ]);

        $routeProgress = $routes->routeProgress($jobCard);

        return view('admin.production.job-cards.floor-display', [
            'jobCard' => $jobCard,
            'routeProgress' => $routeProgress,
            'refreshSeconds' => 30,
        ]);
    }
}
