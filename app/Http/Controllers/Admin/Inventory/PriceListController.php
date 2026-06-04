<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\PriceList;
use App\Support\Catalogue\PriceListService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PriceListController extends Controller
{
    use ResolvesInventoryTenant;

    public function __construct(
        protected PriceListService $priceLists,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('catalogue.view'), 403);

        $priceLists = PriceList::query()->forTenant()->withCount('items')->latest('effective_date')->paginate(15);

        return view('admin.inventory.catalogue.price-lists.index', compact('priceLists'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        return view('admin.inventory.catalogue.price-lists.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.create'), 403);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds();
        $data = $this->validatePriceList($request, $companyId, $branchId);
        unset($data['items']);

        $priceList = PriceList::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $this->priceLists->syncItems($priceList, $request->input('items', []));

        return redirect()->route('admin.inventory.catalogue.price-lists.index')->with('status', __('Price list created.'));
    }

    public function edit(PriceList $priceList): View
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($priceList);

        $priceList->load('items.item');

        return view('admin.inventory.catalogue.price-lists.edit', ['priceList' => $priceList, ...$this->formMeta()]);
    }

    public function update(Request $request, PriceList $priceList): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($priceList);

        $data = $this->validatePriceList($request, $priceList->company_id, $priceList->branch_id, $priceList);
        unset($data['items']);

        $priceList->update($data);
        $this->priceLists->syncItems($priceList, $request->input('items', []));

        return redirect()->route('admin.inventory.catalogue.price-lists.index')->with('status', __('Price list updated.'));
    }

    public function destroy(PriceList $priceList): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.delete'), 403);
        $this->ensureTenant($priceList);

        $priceList->delete();

        return back()->with('status', __('Price list removed.'));
    }

    protected function validatePriceList(Request $request, int $companyId, int $branchId, ?PriceList $priceList = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('price_lists', 'name')->where('company_id', $companyId)->where('branch_id', $branchId)->ignore($priceList)],
            'currency' => ['required', 'string', 'size:3'],
            'effective_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'draft'])],
            'items' => ['nullable', 'array'],
            'items.*.inventory_item_id' => ['nullable', Rule::exists('inventory_items', 'id')->where('company_id', $companyId)->where('branch_id', $branchId)],
            'items.*.price_override' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    protected function formMeta(): array
    {
        return [
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->get(),
        ];
    }

    protected function ensureTenant(PriceList $priceList): void
    {
        abort_unless($priceList->company_id === tenant()->companyId() && $priceList->branch_id === tenant()->branchId(), 404);
    }
}
