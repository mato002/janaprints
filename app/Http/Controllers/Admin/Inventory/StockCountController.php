<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\StockCountType;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryVarianceReasonCode;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\Warehouse;
use App\Support\Export\TabularExportWriter;
use App\Support\Inventory\StockCountService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockCountController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', StockCount::class);

        $counts = $this->scopeToTenant(
            StockCount::query()->with(['warehouse', 'creator'])->latest('count_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.control.stock-counts.index', compact('counts'));
    }

    public function create(): View
    {
        $this->authorize('create', StockCount::class);

        return view('admin.inventory.control.stock-counts.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockCount::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $header = $this->formSettings->validateRequest($request, 'stock_count.create', [
            'warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'count_type' => [Rule::enum(StockCountType::class)],
            'count_date' => ['date'],
            'notes' => ['string', 'max:2000'],
            'item_ids' => ['array'],
            'item_ids.*' => [Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
        ], $companyId, $branchId);
        [$header, $customData] = $this->partitionCustomFields('stock_count.create', $header, $companyId, $branchId);

        $count = StockCountService::create(
            companyId: $companyId,
            branchId: $branchId,
            warehouseId: (int) $header['warehouse_id'],
            countType: StockCountType::from($header['count_type']),
            countDate: $header['count_date'],
            userId: (int) auth()->id(),
            notes: $header['notes'] ?? null,
            itemIds: $header['item_ids'] ?? [],
        );

        $this->syncCustomFields($count, 'stock_count.create', $customData, $companyId);

        return redirect()->route('admin.inventory.stock-counts.worksheet', $count)
            ->with('status', __('Stock count created.'));
    }

    public function show(StockCount $stockCount): View
    {
        $this->authorize('view', $stockCount);

        $stockCount->load(['warehouse', 'creator', 'submitter', 'approver', 'poster', 'items.inventoryItem', 'stockAdjustment', 'reconciliation']);

        return view('admin.inventory.control.stock-counts.show', ['count' => $stockCount]);
    }

    public function worksheet(StockCount $stockCount): View
    {
        $this->authorize('view', $stockCount);

        $stockCount->load(['warehouse', 'items.inventoryItem']);

        ['companyId' => $companyId] = $this->tenantIds();

        $reasonCodes = InventoryVarianceReasonCode::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.inventory.control.stock-counts.worksheet', [
            'count' => $stockCount,
            'reasonCodes' => $reasonCodes,
        ]);
    }

    public function updateWorksheet(Request $request, StockCount $stockCount): RedirectResponse
    {
        $this->authorize('update', $stockCount);

        $lines = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'integer'],
            'items.*.counted_quantity' => ['required', 'numeric', 'min:0'],
            'items.*.inventory_variance_reason_code_id' => ['nullable', 'integer'],
            'items.*.reason' => ['nullable', 'string', 'max:500'],
            'items.*.notes' => ['nullable', 'string', 'max:2000'],
        ])['items'];

        try {
            StockCountService::updateCountedQuantities($stockCount, $lines, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('status', __('Worksheet saved.'));
    }

    public function submit(StockCount $stockCount): RedirectResponse
    {
        $this->authorize('submit', $stockCount);

        try {
            StockCountService::submit($stockCount, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Stock count submitted.'));
    }

    public function approve(StockCount $stockCount): RedirectResponse
    {
        $this->authorize('approve', $stockCount);

        try {
            StockCountService::approve($stockCount, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Stock count approved.'));
    }

    public function post(StockCount $stockCount): RedirectResponse
    {
        $this->authorize('post', $stockCount);

        try {
            StockCountService::post($stockCount, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Variances posted to inventory ledger.'));
    }

    public function cancel(StockCount $stockCount): RedirectResponse
    {
        $this->authorize('cancel', $stockCount);

        try {
            StockCountService::cancel($stockCount, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('admin.inventory.stock-counts.index')
            ->with('status', __('Stock count cancelled.'));
    }

    public function exportWorksheet(StockCount $stockCount, string $format = 'csv'): StreamedResponse
    {
        $this->authorize('view', $stockCount);

        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $stockCount->load('items.inventoryItem');

        $headers = ['Item', 'SKU', 'System Qty', 'Counted Qty', 'Variance', 'Unit Cost', 'Variance Value', 'Reason'];
        $rows = $stockCount->items->map(fn ($line) => [
            $line->inventoryItem?->item_name,
            $line->inventoryItem?->sku,
            $line->system_quantity,
            $line->counted_quantity,
            $line->variance_quantity,
            $line->system_unit_cost,
            $line->variance_value,
            $line->reason,
        ])->all();

        return app(TabularExportWriter::class)->download(
            $format,
            'stock-count-'.$stockCount->count_number,
            $headers,
            $rows,
            __('Stock Count :number', ['number' => $stockCount->count_number]),
            $stockCount->count_date?->format('Y-m-d'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        return [
            'warehouses' => Warehouse::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'countTypes' => StockCountType::cases(),
            'formFields' => $this->formSettings->resolvedFields('stock_count.create', $companyId, $branchId),
        ];
    }
}
