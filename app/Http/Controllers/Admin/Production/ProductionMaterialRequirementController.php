<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\InventoryStockRole;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Support\Production\MaterialRequirementsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionMaterialRequirementController extends Controller
{
    public function __construct(
        protected MaterialRequirementsService $requirementsService,
    ) {}

    public function linkFinishedProduct(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(
            auth()->user()?->can('production.edit')
            || auth()->user()?->can('production.materials.generate'),
            403,
        );

        $validated = $request->validate([
            'inventory_item_id' => [
                'required',
                Rule::exists('inventory_items', 'id')
                    ->where('company_id', $jobCard->company_id)
                    ->where('branch_id', $jobCard->branch_id)
                    ->where('stock_role', InventoryStockRole::FinishedGood->value)
                    ->where('is_active', true),
            ],
        ]);

        $item = InventoryItem::query()->findOrFail($validated['inventory_item_id']);

        $jobCard->update(['inventory_item_id' => $item->id]);

        if ($jobCard->salesOrder) {
            $jobCard->salesOrder->update(['inventory_item_id' => $item->id]);
            $jobCard->salesOrder->items()
                ->whereNull('inventory_item_id')
                ->update(['inventory_item_id' => $item->id]);
        }

        return back()->with(
            'status',
            __('Finished product :item linked. Continue with BOM and generate requirements.', [
                'item' => $item->item_name,
            ]),
        );
    }

    public function generate(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.materials.generate'), 403);

        $workflow = $this->requirementsService->workflowChecklist($jobCard);
        if (! ($workflow['can_generate'] ?? false)) {
            return back()->withErrors([
                'sales_order' => $workflow['blocker']
                    ?? __('Finish the materials workflow steps before generating requirements.'),
            ]);
        }

        $validated = $request->validate([
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)],
        ]);

        try {
            $this->requirementsService->generate(
                $jobCard,
                (int) $validated['warehouse_id'],
                (int) auth()->id(),
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Material requirements generated from BOM.'));
    }

    public function reserveAll(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.materials.reserve'), 403);

        $reserved = $this->requirementsService->reserveAll($jobCard, (int) auth()->id());

        return back()->with('status', __(':count material lines reserved.', ['count' => $reserved->count()]));
    }

    public function reserve(ProductionJobCard $jobCard, ProductionMaterialRequirement $requirement): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.materials.reserve'), 403);
        abort_unless($requirement->production_job_card_id === $jobCard->id, 404);

        try {
            $this->requirementsService->reserve($requirement, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Material reserved for this job.'));
    }

    public function consume(Request $request, ProductionJobCard $jobCard, ProductionMaterialRequirement $requirement): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.materials.consume'), 403);
        abort_unless($requirement->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'numeric', 'min:0.001'],
        ]);

        try {
            $this->requirementsService->consumeFromRequirement(
                $requirement,
                (int) auth()->id(),
                isset($validated['quantity']) ? (float) $validated['quantity'] : null,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Material consumption recorded from requirement.'));
    }
}
