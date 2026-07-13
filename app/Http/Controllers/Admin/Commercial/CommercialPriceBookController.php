<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\CommercialPriceBookStatus;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Commercial\CommercialCustomerPriceBook;
use App\Models\Commercial\CommercialPriceBook;
use App\Models\Commercial\CommercialPriceBookItem;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Support\Commercial\CommercialPriceBookService;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\FormStatusOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialPriceBookController extends Controller
{
    use HandlesModalFormResponses, ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected CommercialPriceBookService $priceBooks,
        protected FormSettingsService $formSettings,
        protected FormStatusOptionService $statusOptions,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommercialPriceBook::class);

        $books = $this->scopeToTenant(CommercialPriceBook::query()->with(['branch:id,name']))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.price-books.index', [
            'priceBooks' => $books,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CommercialPriceBook::class);

        return view('admin.commercial.price-books.create', $this->formMeta($request));
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', CommercialPriceBook::class);

        $data = $this->validateBook($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $book = CommercialPriceBook::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $data['branch_id'] ?? $branchId,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        if ($book->is_default) {
            $this->priceBooks->setAsDefault($book);
        }

        return $this->modalOrRedirect(
            __('Price book created.'),
            redirect()->route('admin.commercial.price-books.show', $book),
        );
    }

    public function show(CommercialPriceBook $priceBook): View
    {
        $this->authorize('view', $priceBook);

        $priceBook->load(['items.inventoryItem:id,item_name,sku', 'customerAssignments.customer:id,company_name', 'branch:id,name']);

        $assignedCustomerIds = $priceBook->customerAssignments->pluck('customer_id')->filter()->values();

        return view('admin.commercial.price-books.show', [
            'priceBook' => $priceBook,
            'inventoryItems' => InventoryItem::query()->forTenant()->orderBy('item_name')->limit(200)->get(['id', 'item_name', 'sku']),
            'customers' => Customer::query()
                ->where('company_id', $priceBook->company_id)
                ->when($assignedCustomerIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $assignedCustomerIds))
                ->orderBy('company_name')
                ->get(['id', 'company_name']),
        ]);
    }

    public function edit(CommercialPriceBook $priceBook): View
    {
        $this->authorize('update', $priceBook);

        return view('admin.commercial.price-books.edit', array_merge(
            ['priceBook' => $priceBook],
            $this->formMeta(request(), $priceBook),
        ));
    }

    public function update(Request $request, CommercialPriceBook $priceBook): RedirectResponse|Response
    {
        $this->authorize('update', $priceBook);

        $data = $this->validateBook($request, $priceBook);
        $priceBook->update([
            ...$data,
            'updated_by' => $request->user()->id,
        ]);

        if ($priceBook->is_default) {
            $this->priceBooks->setAsDefault($priceBook);
        }

        return $this->modalOrRedirect(
            __('Price book updated.'),
            redirect()->route('admin.commercial.price-books.show', $priceBook),
        );
    }

    public function destroy(CommercialPriceBook $priceBook): RedirectResponse
    {
        $this->authorize('delete', $priceBook);

        $priceBook->delete();

        return redirect()->route('admin.commercial.price-books.index')->with('status', __('Price book removed.'));
    }

    public function storeItem(Request $request, CommercialPriceBook $priceBook): RedirectResponse
    {
        $this->authorize('update', $priceBook);

        $validated = $request->validate([
            'inventory_item_id' => ['nullable', 'required_without:service_code', 'exists:inventory_items,id'],
            'service_code' => ['nullable', 'required_without:inventory_item_id', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'minimum_quantity' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::enum(CommercialPriceBookStatus::class)],
        ]);

        $validated['status'] = $validated['status'] ?? CommercialPriceBookStatus::Active;

        $priceBook->items()->create($validated);

        return back()->with('status', __('Price book item added.'));
    }

    public function destroyItem(CommercialPriceBook $priceBook, CommercialPriceBookItem $item): RedirectResponse
    {
        $this->authorize('update', $priceBook);
        abort_unless($item->price_book_id === $priceBook->id, 404);

        $item->delete();

        return back()->with('status', __('Price book item removed.'));
    }

    public function assignCustomer(Request $request, CommercialPriceBook $priceBook): RedirectResponse
    {
        $this->authorize('update', $priceBook);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->priceBooks->assignCustomerPriceBook(
            $priceBook->company_id,
            (int) $validated['customer_id'],
            $priceBook->id,
            isset($validated['starts_at']) ? \Illuminate\Support\Carbon::parse($validated['starts_at']) : null,
            isset($validated['ends_at']) ? \Illuminate\Support\Carbon::parse($validated['ends_at']) : null,
        );

        return back()->with('status', __('Customer price book assigned.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateBook(Request $request, ?CommercialPriceBook $book = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $serverProvidedFields = $this->formSettings->isVisible('commercial_price_book.create', 'branch_id', $companyId, $branchId)
            ? []
            : ['branch_id'];

        return $this->formSettings->validateRequest($request, 'commercial_price_book.create', [
            'name' => ['string', 'max:120'],
            'code' => [
                'string', 'max:40',
                Rule::unique('commercial_price_books', 'code')
                    ->where('company_id', $companyId)
                    ->ignore($book?->id),
            ],
            'description' => ['string', 'max:2000'],
            'currency' => ['string', 'size:3'],
            'branch_id' => ['exists:branches,id'],
            'status' => $this->statusOptions->validationRules('commercial_price_book.create', $companyId, $branchId, false),
            'starts_at' => ['date'],
            'ends_at' => ['date', 'after_or_equal:starts_at'],
            'is_default' => ['sometimes', 'boolean'],
        ], $companyId, $branchId, $serverProvidedFields);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(Request $request, ?CommercialPriceBook $priceBook = null): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        return [
            'formFields' => $this->formSettings->resolvedFields('commercial_price_book.create', $companyId, $branchId, $priceBook),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->limit(100)->get(['id', 'company_name']),
        ];
    }
}
