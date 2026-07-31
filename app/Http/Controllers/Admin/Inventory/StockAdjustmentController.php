<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Enums\DocumentType;
use App\Enums\StockAdjustmentStatus;
use App\Enums\StockAdjustmentDirection;
use App\Http\Controllers\Admin\Concerns\HandlesFormCustomFields;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\Warehouse;
use App\Support\Inventory\ReturnsToStoreDesk;
use App\Support\Inventory\StoreDeskViews;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberingService;
use App\Support\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    use HandlesFormCustomFields, ResolvesInventoryTenant, ReturnsToStoreDesk, ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', StockAdjustment::class);

        return redirect()->to(StoreDeskViews::deskUrl(StoreDeskViews::ADJUSTMENTS, $request->query()));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StockAdjustment::class);

        return view('admin.inventory.adjustments.create', [
            ...$this->formMeta(),
            'fromStoreDesk' => $this->wantsStoreDeskReturn($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockAdjustment::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();

        $header = $this->formSettings->validateRequest($request, 'stock_adjustment.create', [
            'warehouse_id' => [Rule::exists('warehouses', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)->where('is_active', true)],
            'adjustment_date' => ['date'],
            'reason' => ['string', 'max:2000'],
            'notes' => ['string'],
        ], $companyId, $branchId);
        [$header, $customData] = $this->partitionCustomFields('stock_adjustment.create', $header, $companyId, $branchId);

        $lines = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.direction' => ['required', Rule::enum(StockAdjustmentDirection::class)],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ])['items'];

        $adjustment = StockAdjustment::query()->create([
            ...$header,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'adjustment_number' => app(NumberingService::class)->next(
                DocumentType::StockAdjustment,
                $companyId,
                $branchId,
            ),
            'status' => StockAdjustmentStatus::Draft,
            'adjusted_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            $adjustment->items()->create($line);
        }

        $this->syncCustomFields($adjustment, 'stock_adjustment.create', $customData, $companyId);

        \App\Support\ActivityLogger::log('inventory_adjusted', $adjustment);

        if ($this->wantsStoreDeskReturn($request)) {
            return redirect()->to($this->storeDeskUrl())->with('status', __('Adjustment created.'));
        }

        return redirect()->route('admin.inventory.adjustments.show', $adjustment)->with('status', __('Adjustment created.'));
    }

    public function show(StockAdjustment $adjustment): View
    {
        $this->authorize('view', $adjustment);

        $adjustment->load(['warehouse', 'adjuster', 'items.inventoryItem', 'submitter', 'approver']);

        return view('admin.inventory.adjustments.show', compact('adjustment'));
    }

    public function submit(StockAdjustment $adjustment): RedirectResponse
    {
        $this->authorize('submit', $adjustment);

        try {
            StockAdjustmentService::submit($adjustment, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Adjustment submitted for approval.'));
    }

    public function approve(StockAdjustment $adjustment): RedirectResponse
    {
        $this->authorize('approve', $adjustment);

        try {
            StockAdjustmentService::approve($adjustment, (int) auth()->id(), request()->input('approval_reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Adjustment approved.'));
    }

    public function post(StockAdjustment $adjustment): RedirectResponse
    {
        $this->authorize('post', $adjustment);

        try {
            StockAdjustmentService::post($adjustment, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Adjustment posted.'));
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
            'directions' => StockAdjustmentDirection::cases(),
            'formFields' => $this->formSettings->resolvedFields('stock_adjustment.create', $companyId, $branchId),
        ];
    }
}
