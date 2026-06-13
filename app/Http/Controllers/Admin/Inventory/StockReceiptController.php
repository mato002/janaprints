<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockReceiptSource;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\Warehouse;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use App\Support\StockReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockReceiptController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', StockReceipt::class);

        $receipts = $this->scopeToTenant(
            StockReceipt::query()->with(['warehouse', 'receiver'])->latest('receipt_date')
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.inventory.receipts.index', compact('receipts'));
    }

    public function create(): View
    {
        $this->authorize('create', StockReceipt::class);

        return view('admin.inventory.receipts.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockReceipt::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $header = $this->validateHeader($request, $companyId, $branchId);
        [$header, $customData] = $this->partitionCustomFields('stock_receipt.create', $header, $companyId, $branchId);
        $lines = $this->validateLines($request, $companyId, $branchId);

        $receipt = StockReceipt::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'receipt_number' => $this->nextNumber($companyId, $branchId),
            'status' => InventoryDocumentStatus::Draft,
            'received_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $receipt->items()->create($line);
        }

        $this->syncCustomFields($receipt, 'stock_receipt.create', $customData, $companyId);

        return redirect()->route('admin.inventory.receipts.show', $receipt)->with('status', __('Receipt created.'));
    }

    public function show(StockReceipt $receipt): View
    {
        $this->authorize('view', $receipt);

        $receipt->load(['warehouse', 'receiver', 'items.inventoryItem']);

        return view('admin.inventory.receipts.show', compact('receipt'));
    }

    public function post(StockReceipt $receipt): RedirectResponse
    {
        $this->authorize('post', $receipt);

        try {
            StockReceiptService::post($receipt, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Receipt posted to inventory.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request, int $companyId, int $branchId): array
    {
        return $request->validate($this->formSettings->mergeValidationRules('stock_receipt.create', [
            'warehouse_id' => [
                Rule::exists('warehouses', 'id')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->where('is_virtual', false),
            ],
            'source' => [Rule::enum(StockReceiptSource::class)],
            'receipt_date' => ['date'],
            'notes' => ['string'],
        ], $companyId, $branchId));
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validateLines(Request $request, int $companyId, int $branchId): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        return $validated['items'];
    }

    protected function nextNumber(int $companyId, int $branchId): string
    {
        return app(NumberingService::class)->next(
            DocumentType::StockReceipt,
            $companyId,
            $branchId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        return [
            'warehouses' => Warehouse::query()->forTenant()->physical()->where('is_active', true)->orderBy('name')->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'sources' => StockReceiptSource::cases(),
            'formFields' => $this->formSettings->resolvedFields('stock_receipt.create', $companyId, $branchId),
        ];
    }
}
