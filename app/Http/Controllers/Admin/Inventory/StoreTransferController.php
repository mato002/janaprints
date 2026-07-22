<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\DocumentType;
use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\ReturnsToStoreDesk;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use App\Support\StockIssueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreTransferController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ReturnsToStoreDesk, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', StockIssue::class);

        $query = $this->scopeToTenant(
            StockIssue::query()
                ->with(['warehouse', 'toWarehouse', 'issuer'])
                ->where('destination', StockIssueDestination::Transfer)
                ->latest('issue_date')
        );

        if ($warehouseId = request('warehouse_id')) {
            $query->where(fn ($q) => $q->where('warehouse_id', $warehouseId)->orWhere('to_warehouse_id', $warehouseId));
        }

        $transfers = $query->paginate(config('platform.pagination.default', 15));
        $warehouses = Warehouse::query()->forTenant()->orderBy('name')->get();
        $statuses = InventoryDocumentStatus::cases();

        return view('admin.inventory.transfers.index', compact('transfers', 'warehouses', 'statuses'));
    }

    public function create(Request $request): View
    {
        abort_unless(auth()->user()?->can('inventory.transfer'), 403);

        return view('admin.inventory.transfers.create', [
            ...$this->formMeta(),
            'fromStoreDesk' => $this->wantsStoreDeskReturn($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('inventory.transfer'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $header = $this->formSettings->validateRequest($request, 'store_transfer.create', [
            'warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'to_warehouse_id' => ['different:warehouse_id', Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'issue_date' => ['date'],
            'notes' => ['string'],
        ], $companyId, $branchId);
        [$header, $customData] = $this->partitionCustomFields('store_transfer.create', $header, $companyId, $branchId);

        $lines = $this->validateLines($request, $companyId, $branchId);

        $transfer = StockIssue::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'issue_number' => app(NumberingService::class)->next(DocumentType::StockIssue, $companyId, $branchId),
            'destination' => StockIssueDestination::Transfer,
            'status' => InventoryDocumentStatus::Draft,
            'issued_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $transfer->items()->create($line);
        }

        $this->syncCustomFields($transfer, 'store_transfer.create', $customData, $companyId);

        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl())->with('status', __('Store transfer created.'));
        }

        return redirect()->route('admin.inventory.transfers.show', $transfer)->with('status', __('Store transfer created.'));
    }

    public function show(StockIssue $transfer): View
    {
        abort_unless($transfer->destination === StockIssueDestination::Transfer, 404);
        $this->authorize('view', $transfer);

        $transfer->load(['warehouse', 'toWarehouse', 'issuer', 'items.inventoryItem']);

        return view('admin.inventory.transfers.show', compact('transfer'));
    }

    public function post(StockIssue $transfer): RedirectResponse
    {
        abort_unless($transfer->destination === StockIssueDestination::Transfer, 404);
        $this->authorize('post', $transfer);

        try {
            StockIssueService::post($transfer, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Store transfer posted.'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validateLines(Request $request, int $companyId, int $branchId): array
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['nullable', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lines = collect($validated['items'])
            ->filter(fn (array $line) => filled($line['inventory_item_id'] ?? null)
                || filled($line['quantity'] ?? null)
                || filled($line['unit_cost'] ?? null))
            ->values();

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => __('Transfer must have at least one line.'),
            ]);
        }

        foreach ($lines as $index => $line) {
            if (! filled($line['inventory_item_id'] ?? null) || ! filled($line['quantity'] ?? null) || ! filled($line['unit_cost'] ?? null)) {
                throw ValidationException::withMessages([
                    "items.{$index}" => __('Each transfer line needs an item, quantity, and unit cost.'),
                ]);
            }
        }

        return $lines->all();
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
            'formFields' => $this->formSettings->resolvedFields('store_transfer.create', $companyId, $branchId),
        ];
    }
}
