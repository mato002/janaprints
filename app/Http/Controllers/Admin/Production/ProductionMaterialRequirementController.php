<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\InventoryStockRole;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Support\Production\MaterialRequirementsService;
use App\Support\Production\ProductBomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionMaterialRequirementController extends Controller
{
    public function __construct(
        protected MaterialRequirementsService $requirementsService,
        protected ProductBomService $bomService,
    ) {}

    public function storeBom(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(
            auth()->user()?->can('production.edit')
            || auth()->user()?->can('production.bom.create'),
            403,
        );

        try {
            $header = $request->validate([
                'finished_item_id' => [
                    'required',
                    Rule::exists('inventory_items', 'id')
                        ->where('company_id', $jobCard->company_id)
                        ->where('branch_id', $jobCard->branch_id),
                ],
                'name' => ['required', 'string', 'max:120'],
                'version' => ['nullable', 'integer', 'min:1'],
                'is_active' => ['nullable', 'boolean'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]);

            $lines = $request->validate([
                'lines' => ['required', 'array', 'min:1'],
                'lines.*.inventory_item_id' => [
                    'required',
                    Rule::exists('inventory_items', 'id')
                        ->where('company_id', $jobCard->company_id)
                        ->where('branch_id', $jobCard->branch_id),
                ],
                'lines.*.quantity_per_unit' => ['required', 'numeric', 'min:0.0001'],
                'lines.*.waste_factor_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'lines.*.notes' => ['nullable', 'string', 'max:255'],
            ])['lines'];

            $this->bomService->create(
                (int) $jobCard->company_id,
                (int) $jobCard->branch_id,
                (int) auth()->id(),
                $header,
                $lines,
            );
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials'])
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials'])
            ->with('status', __('Bill of materials created. Generate requirements next.'));
    }

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
        abort_unless(
            auth()->user()?->can('production.materials.generate')
            || auth()->user()?->can('production.edit'),
            403,
        );

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
                true,
                true,
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
        abort_unless(auth()->user()?->can('inventory.issue'), 403);
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

    public function consumeAll(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.materials.consume'), 403);
        abort_unless(auth()->user()?->can('inventory.issue'), 403);

        $result = $this->requirementsService->consumeAll($jobCard, (int) auth()->id());
        $consumed = (int) ($result['consumed'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? 0);

        if ($consumed === 0 && $skipped > 0) {
            return back()->withErrors([
                'materials' => __('No lines consumed. Receive stock into a physical warehouse, then try again.'),
            ]);
        }

        $message = $skipped > 0
            ? __(':count material lines consumed. :skipped skipped (no stock or already complete).', [
                'count' => $consumed,
                'skipped' => $skipped,
            ])
            : __(':count material lines consumed.', ['count' => $consumed]);

        return back()->with('status', $message);
    }
}
