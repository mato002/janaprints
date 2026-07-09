<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\PosPaymentMethod;
use App\Enums\PosSaleStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Inventory\InventoryItem;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleHold;
use App\Support\Commercial\PosCounterSalesPresenter;
use App\Support\Commercial\PosSaleCalculator;
use App\Support\Commercial\PosSaleService;
use App\Support\Commercial\PosSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosSaleController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected PosSaleService $posSales,
        protected PosSaleCalculator $calculator,
        protected PosSessionService $posSessions,
        protected PosCounterSalesPresenter $presenter,
    ) {}

    public function dashboard(): View
    {
        $this->authorize('viewAny', PosSale::class);

        $base = $this->scopeToTenant(PosSale::query());
        $today = (clone $base)->whereDate('sale_date', today());

        $stats = [
            'sales_today' => (clone $today)->where('status', PosSaleStatus::Paid)->count(),
            'revenue_today' => (clone $today)->where('status', PosSaleStatus::Paid)->sum('total_amount'),
            'held' => (clone $base)->where('status', PosSaleStatus::Held)->count(),
            'draft' => (clone $base)->where('status', PosSaleStatus::Draft)->count(),
        ];

        $recent = (clone $base)
            ->with(['customer:id,company_name', 'cashier:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        $heldQueue = $this->scopeToTenant(
            PosSaleHold::query()
                ->with(['sale', 'customer:id,company_name', 'cashier:id,name'])
        )
            ->whereHas('sale', fn ($q) => $q->where('status', PosSaleStatus::Held))
            ->latest('held_at')
            ->limit(10)
            ->get();

        $sessionWidget = ['session' => null, 'metrics' => null];

        if (auth()->check()) {
            ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds(request());
            $sessionWidget = $this->posSessions->currentCashierSessionWidget(
                $companyId,
                $branchId,
                (int) auth()->id(),
            );
        }

        return view('admin.commercial.pos.dashboard', compact('stats', 'recent', 'heldQueue', 'sessionWidget'));
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PosSale::class);

        $sales = $this->scopeToTenant(
            PosSale::query()->with(['customer:id,company_name', 'cashier:id,name'])
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('sale_date', $request->date('date')))
            ->when(! $request->filled('date'), fn ($q) => $q->whereDate('sale_date', today()))
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.pos.index', [
            'sales' => $sales,
            'filters' => $request->only(['status', 'date']),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $this->authorize('create', PosSale::class);

        return redirect()
            ->route('admin.commercial.pos.counter-sales')
            ->with('status', __('The legacy POS screen has been retired. Use Counter Sales for new transactions.'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', PosSale::class);

        $payload = $this->validateSalePayload($request);

        if ($payload['action'] === 'hold') {
            $this->authorize('hold', PosSale::class);
        }

        if ($payload['action'] === 'pay') {
            $this->authorize('create', PosSale::class);
            abort_unless(auth()->user()->can('pos.counter_sales.complete') || auth()->user()->can('pos.create'), 403);
        }

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $session = $this->posSessions->requireOpenSession($companyId, $branchId, (int) auth()->id());
        $this->posSessions->assertSessionAcceptsSales($session);

        $sale = $this->posSales->createSale(
            $payload,
            $companyId,
            $branchId,
            (int) auth()->id(),
            (int) $session->id,
        );

        if ($request->wantsJson()) {
            if ($payload['action'] === 'hold') {
                return response()->json([
                    'message' => __('Sale held (:number).', ['number' => $sale->sale_number]),
                    'sale_id' => $sale->id,
                ]);
            }

            return response()->json([
                'message' => __('Sale saved.'),
                'receipt' => $this->presenter->receiptPayload($sale->fresh(['items', 'payments', 'customer', 'cashier', 'branch'])),
            ]);
        }

        if ($payload['action'] === 'hold') {
            return redirect()
                ->route('admin.commercial.pos.counter-sales')
                ->with('status', __('Sale held (:number).', ['number' => $sale->sale_number]));
        }

        return redirect()
            ->route('admin.commercial.pos.receipt', $sale)
            ->with('status', __('Sale saved.'));
    }

    public function show(PosSale $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['items.inventoryItem', 'payments', 'customer', 'cashier', 'hold', 'returns']);

        return view('admin.commercial.pos.show', compact('sale'));
    }

    public function receipt(PosSale $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['items', 'payments', 'customer', 'cashier', 'branch']);

        return view('admin.commercial.pos.receipt', compact('sale'));
    }

    public function holds(): View
    {
        $this->authorize('viewAny', PosSale::class);

        $holds = $this->scopeToTenant(
            PosSaleHold::query()
                ->with(['sale.items', 'customer:id,company_name', 'cashier:id,name'])
        )
            ->latest('held_at')
            ->paginate(20);

        return view('admin.commercial.pos.holds', compact('holds'));
    }

    public function resume(Request $request, PosSale $sale): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('complete', $sale) || auth()->user()->can('update', $sale),
            403,
        );

        abort_unless($sale->status === PosSaleStatus::Held, 404);

        return redirect()->route('admin.commercial.pos.counter-sales', ['resume' => $sale->id]);
    }

    public function pay(Request $request, PosSale $sale): RedirectResponse|JsonResponse
    {
        $this->authorize('complete', $sale);

        abort_unless($sale->status === PosSaleStatus::Held, 404);

        $payload = $this->validateHeldPayPayload($request);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $session = $this->posSessions->requireOpenSession($companyId, $branchId, (int) auth()->id());
        $this->posSessions->assertSessionAcceptsSales($session);

        $paid = $this->posSales->payHeldSale($sale, $payload);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => __('Held sale :number completed.', ['number' => $paid->sale_number]),
                'receipt' => $this->presenter->receiptPayload($paid->fresh(['items', 'payments', 'customer', 'cashier', 'branch'])),
            ]);
        }

        return redirect()
            ->route('admin.commercial.pos.receipt', $paid)
            ->with('status', __('Held sale :number completed.', ['number' => $paid->sale_number]));
    }

    public function cancel(Request $request, PosSale $sale): RedirectResponse|JsonResponse
    {
        $this->authorize('cancel', $sale);

        $sale->update(['status' => PosSaleStatus::Cancelled]);
        $sale->hold?->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => __('Sale cancelled.')]);
        }

        return back()->with('status', __('Sale cancelled.'));
    }

    public function refund(PosSale $sale): RedirectResponse
    {
        $this->authorize('refund', $sale);

        abort_unless($sale->status === PosSaleStatus::Paid, 422);

        $sale->update(['status' => PosSaleStatus::Refunded]);

        return back()->with('status', __('Sale marked as refunded.'));
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    protected function heldCartPayload(PosSale $sale): array
    {
        return [
            'lines' => $sale->items->map(fn ($item) => [
                'item_id' => $item->inventory_item_id ?? '',
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_amount' => (float) $item->discount_amount,
                'tax_amount' => (float) $item->tax_amount,
            ])->values()->all(),
            'saleDiscount' => (float) $sale->discount_amount,
            'saleTax' => (float) $sale->tax_amount,
            'walkIn' => $sale->is_walk_in,
            'customerId' => $sale->customer_id ?? '',
        ];
    }

    protected function saleFormMeta(): array
    {
        return [
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']),
            'items' => InventoryItem::query()->forTenant()->where('is_active', true)->orderBy('item_name')->limit(200)->get(),
            'paymentMethods' => PosPaymentMethod::cases(),
            'previewTotals' => $this->calculator->totals([]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSalePayload(Request $request): array
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['save', 'hold', 'pay'])],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'is_walk_in' => ['sometimes', 'boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::enum(PosPaymentMethod::class)],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'hold_label' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['action'] === 'pay' && empty($data['payment_method'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'payment_method' => __('Select a payment method to complete the sale.'),
            ]);
        }

        $status = match ($data['action']) {
            'hold' => PosSaleStatus::Held,
            'pay' => PosSaleStatus::Paid,
            default => PosSaleStatus::Draft,
        };

        $totals = $this->calculator->totals(
            $data['lines'],
            $data['discount_amount'] ?? 0,
            $data['tax_amount'] ?? 0,
        );

        return [
            'customer_id' => $data['customer_id'] ?? null,
            'is_walk_in' => $request->boolean('is_walk_in'),
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'status' => $status,
            'amount_paid' => $data['action'] === 'pay' ? $totals['total_amount'] : 0,
            'hold_label' => $data['hold_label'] ?? null,
            'notes' => $data['notes'] ?? null,
            'lines' => $data['lines'],
            'action' => $data['action'],
            'payment_method' => $data['payment_method'] ?? null,
            'payment_reference' => $data['payment_reference'] ?? null,
            'payments' => $data['action'] === 'pay' ? [[
                'payment_method' => $data['payment_method'],
                'amount' => $totals['total_amount'],
                'reference' => $data['payment_reference'] ?? null,
            ]] : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeldPayPayload(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'is_walk_in' => ['sometimes', 'boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::enum(PosPaymentMethod::class)],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return [
            'customer_id' => $data['customer_id'] ?? null,
            'is_walk_in' => $request->boolean('is_walk_in'),
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'lines' => $data['lines'],
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? null,
        ];
    }
}
