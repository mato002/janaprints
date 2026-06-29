<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use App\Models\Production\ProductionJobCard;
use App\Support\ProductionMaterialConsumptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionMaterialConsumptionController extends Controller
{
    public function store(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);

        if (! auth()->user()->can('inventory.issue')) {
            abort(403);
        }

        abort_unless(auth()->user()?->can('production.materials.consume'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = [
            'companyId' => $jobCard->company_id,
            'branchId' => $jobCard->branch_id,
        ];

        $validated = $request->validate([
            'inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'warehouse_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $warehouse = Warehouse::query()
            ->where('id', $validated['warehouse_id'])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->first();

        if ($warehouse === null) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Warehouse not found or inactive.'),
            ]);
        }

        if ($warehouse->is_virtual) {
            throw ValidationException::withMessages([
                'warehouse_id' => __('Consume raw materials from a physical warehouse. Virtual locations such as Finished Goods only hold completed output.'),
            ]);
        }

        $item = InventoryItem::query()->findOrFail($validated['inventory_item_id']);

        try {
            ProductionMaterialConsumptionService::consume(
                $jobCard,
                $item,
                (int) $validated['warehouse_id'],
                (float) $validated['quantity'],
                (int) auth()->id(),
                isset($validated['unit_cost']) ? (float) $validated['unit_cost'] : null,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Material consumption recorded.'));
    }
}
