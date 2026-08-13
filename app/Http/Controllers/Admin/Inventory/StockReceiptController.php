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
use App\Models\Production\ProductionJobCard;
use App\Support\Inventory\ReturnsToStoreDesk;
use App\Support\Inventory\StoreDeskViews;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use App\Support\Production\MaterialRequirementsService;
use App\Support\StockReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockReceiptController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ReturnsToStoreDesk, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
        protected MaterialRequirementsService $materialRequirements,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', StockReceipt::class);

        return redirect()->to(StoreDeskViews::deskUrl(StoreDeskViews::RECEIPTS, $request->query()));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StockReceipt::class);

        $jobCard = $this->resolveJobCard($request);
        $prefill = $jobCard
            ? $this->materialRequirements->stockReceiptPrefill($jobCard)
            : ['lines' => [], 'warehouse_id' => null];

        return view('admin.inventory.receipts.create', [
            ...$this->formMeta(),
            'fromStoreDesk' => $this->wantsStoreDeskReturn($request),
            'sourceJobCard' => $jobCard,
            'prefilledLines' => $prefill['lines'],
            'selectedWarehouseId' => old('warehouse_id', $prefill['warehouse_id'] ?? $this->defaultReceiptWarehouseId()),
            'prefilledNotes' => $jobCard
                ? __('Material shortfall for job :job', ['job' => $jobCard->job_card_number])
                : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockReceipt::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $header = $this->validateHeader($request, $companyId, $branchId);
        [$header, $customData] = $this->partitionCustomFields('stock_receipt.create', $header, $companyId, $branchId);
        $jobCard = $this->resolveJobCard($request);
        if ($jobCard && blank($header['notes'] ?? null)) {
            $header['notes'] = __('Material shortfall for job :job', ['job' => $jobCard->job_card_number]);
        }
        $lines = $this->validateLines($request, $companyId, $branchId);
        $shouldPost = $request->input('intent') === 'post';

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

        $message = __('Receipt saved as draft. Post it to update stock.');

        if ($shouldPost) {
            $this->authorize('post', $receipt);

            try {
                StockReceiptService::post($receipt->fresh(['items', 'warehouse']), (int) auth()->id());
                $message = __('Goods received and posted to stock.');
            } catch (ValidationException $e) {
                return $this->receiptReturnRedirect($request, $receipt)
                    ->with('status', __('Receipt saved as draft, but could not be posted.'))
                    ->withErrors($e->errors());
            }
        }

        return $this->receiptReturnRedirect($request, $receipt)->with('status', $message);
    }

    public function show(Request $request, StockReceipt $receipt): View
    {
        $this->authorize('view', $receipt);

        $receipt->load(['warehouse', 'receiver', 'items.inventoryItem']);

        if ($this->wantsStoreDeskReturn($request)) {
            return view('admin.store.desk.receipt-modal', compact('receipt'));
        }

        return view('admin.inventory.receipts.show', [
            'receipt' => $receipt,
            'fromStoreDesk' => false,
        ]);
    }

    public function post(Request $request, StockReceipt $receipt): RedirectResponse
    {
        $this->authorize('post', $receipt);

        try {
            StockReceiptService::post($receipt, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl())->with('status', __('Receipt posted to inventory.'));
        }

        return back()->with('status', __('Receipt posted to inventory.'));
    }

    protected function receiptReturnRedirect(Request $request, StockReceipt $receipt): RedirectResponse
    {
        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl());
        }

        $jobCard = $this->resolveJobCard($request);
        if ($jobCard && ($request->user()?->can('view', $jobCard) ?? false)) {
            return redirect()->route('admin.production.job-cards.show', [
                'jobCard' => $jobCard,
                'tab' => 'materials',
            ]);
        }

        return redirect()->route('admin.inventory.receipts.show', $receipt);
    }

    protected function resolveJobCard(Request $request): ?ProductionJobCard
    {
        if (! $request->filled('job_card_id')) {
            return null;
        }

        return ProductionJobCard::query()
            ->forTenant()
            ->where(function ($query) use ($request) {
                $query->where('public_id', $request->input('job_card_id'))
                    ->orWhere('id', $request->input('job_card_id'));
            })
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request, int $companyId, int $branchId): array
    {
        return $this->formSettings->validateRequest(
            $request,
            'stock_receipt.create',
            [
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
            ],
            $companyId,
            $branchId,
        );
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
            'warehouses' => Warehouse::query()
                ->forTenant()
                ->physical()
                ->where('is_active', true)
                ->orderByRaw("CASE WHEN code = 'MAIN' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->get(),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
            'sources' => StockReceiptSource::cases(),
            'formFields' => $this->formSettings->resolvedFields('stock_receipt.create', $companyId, $branchId),
        ];
    }

    protected function defaultReceiptWarehouseId(): ?int
    {
        return Warehouse::query()
            ->forTenant()
            ->physical()
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN code = 'MAIN' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->value('id');
    }
}
