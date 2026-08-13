<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Production\PrintProductTemplate;
use App\Models\Production\ProductionSpecification;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Support\Production\PrintProductTemplateService;
use App\Support\Production\ProductionSpecificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionSpecificationController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected ProductionSpecificationService $specifications,
        protected PrintProductTemplateService $printTemplates,
    ) {}

    public function create(Request $request, SalesOrder $salesOrder, SalesOrderItem $salesOrderItem): View
    {
        $this->ensureItemBelongsToOrder($salesOrder, $salesOrderItem);
        $this->authorize('create', [ProductionSpecification::class, $salesOrder]);

        $existing = $this->specifications->findForSalesOrderItem($salesOrderItem);

        if ($existing) {
            return view('admin.production.specifications.edit', $this->formPayload($request, $salesOrder, $salesOrderItem, $existing));
        }

        return view('admin.production.specifications.create', $this->formPayload($request, $salesOrder, $salesOrderItem));
    }

    public function edit(Request $request, SalesOrder $salesOrder, SalesOrderItem $salesOrderItem, ProductionSpecification $specification): View
    {
        $this->ensureItemBelongsToOrder($salesOrder, $salesOrderItem);
        $this->ensureSpecBelongsToItem($salesOrderItem, $specification);
        $this->authorize('update', $specification);

        return view('admin.production.specifications.edit', $this->formPayload($request, $salesOrder, $salesOrderItem, $specification));
    }

    public function store(Request $request, SalesOrder $salesOrder, SalesOrderItem $salesOrderItem): RedirectResponse
    {
        $this->ensureItemBelongsToOrder($salesOrder, $salesOrderItem);
        $this->authorize('create', [ProductionSpecification::class, $salesOrder]);

        $validated = $this->validatedSpecificationPayload($request);

        $this->specifications->createForSalesOrderItem($salesOrderItem, $validated, $request->user());

        return redirect()
            ->route('admin.sales-orders.show', ['salesOrder' => $salesOrder, 'tab' => 'specifications'])
            ->with('status', __('Production specification saved.'));
    }

    public function update(
        Request $request,
        SalesOrder $salesOrder,
        SalesOrderItem $salesOrderItem,
        ProductionSpecification $specification,
    ): RedirectResponse {
        $this->ensureItemBelongsToOrder($salesOrder, $salesOrderItem);
        $this->ensureSpecBelongsToItem($salesOrderItem, $specification);
        $this->authorize('update', $specification);

        $validated = $this->validatedSpecificationPayload($request, $specification);

        $this->specifications->update($specification, $validated, $request->user());

        return redirect()
            ->route('admin.sales-orders.show', ['salesOrder' => $salesOrder, 'tab' => 'specifications'])
            ->with('status', __('Production specification updated.'));
    }

    public function printForSalesOrder(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load([
            'customer',
            'company:id,name,phone,email,address',
            'branch:id,name',
            'items.productionSpecification.paperInventoryItem:id,item_name',
            'items.productionSpecification.materialInventoryItem:id,item_name',
            'items.productionSpecification.inkProfile:id,name',
        ]);

        $lines = $salesOrder->items->map(fn (SalesOrderItem $item) => [
            'item' => $item,
            'specification' => $this->specifications->present($item->productionSpecification),
        ]);

        return view('admin.sales.orders.specifications-print', [
            'salesOrder' => $salesOrder,
            'lines' => $lines,
            'autoPrint' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedSpecificationPayload(Request $request, ?ProductionSpecification $existing = null): array
    {
        $validated = $request->validate($this->specifications->validationRules($existing));

        if (! empty($validated['print_product_template_id'])) {
            $template = PrintProductTemplate::query()
                ->forTenant()
                ->active()
                ->find($validated['print_product_template_id']);

            if ($template) {
                $defaults = $this->printTemplates->applyToSpecificationDefaults($template);

                return $this->printTemplates->mergeWithUserInput($defaults, $validated);
            }
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    protected function formPayload(
        Request $request,
        SalesOrder $salesOrder,
        SalesOrderItem $salesOrderItem,
        ?ProductionSpecification $specification = null,
    ): array {
        $templateDefaults = [];
        $selectedTemplateId = $request->query('template_id') ?? $specification?->print_product_template_id;

        if ($selectedTemplateId && ! $specification) {
            $template = PrintProductTemplate::query()->forTenant()->active()->find($selectedTemplateId);

            if ($template) {
                $templateDefaults = $this->printTemplates->applyToSpecificationDefaults($template);
            }
        }

        return [
            'salesOrder' => $salesOrder,
            'salesOrderItem' => $salesOrderItem,
            'specification' => $specification,
            'templateDefaults' => $templateDefaults,
            'selectedTemplateId' => $selectedTemplateId,
            'printTemplates' => $this->printTemplates->activeForSelection(),
            'productionTypes' => \App\Enums\ProductionType::cases(),
            'inkTypes' => \App\Enums\PrintInkType::cases(),
            'approvalStatuses' => \App\Enums\ProductionSpecificationApprovalStatus::cases(),
            'paperItems' => \App\Models\Inventory\InventoryItem::query()
                ->forTenant()
                ->where('is_active', true)
                ->whereHas('category', fn ($q) => $q->where('code', 'PAPER'))
                ->orderBy('item_name')
                ->get(['id', 'item_name', 'sku']),
            'materialItems' => \App\Models\Inventory\InventoryItem::query()
                ->forTenant()
                ->where('is_active', true)
                ->orderBy('item_name')
                ->limit(200)
                ->get(['id', 'item_name', 'sku']),
            'inkProfiles' => \App\Models\PrintingIntelligence\PrintInkProfile::query()
                ->forTenant()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'ink_type']),
        ];
    }

    protected function ensureItemBelongsToOrder(SalesOrder $salesOrder, SalesOrderItem $salesOrderItem): void
    {
        abort_unless((int) $salesOrderItem->sales_order_id === (int) $salesOrder->id, 404);
    }

    protected function ensureSpecBelongsToItem(SalesOrderItem $salesOrderItem, ProductionSpecification $specification): void
    {
        abort_unless((int) $specification->sales_order_item_id === (int) $salesOrderItem->id, 404);
    }
}
