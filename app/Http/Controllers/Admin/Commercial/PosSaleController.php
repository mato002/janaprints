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
use App\Support\Commercial\PosSaleCalculator;
use App\Support\Commercial\PosSaleService;
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

        return view('admin.commercial.pos.dashboard', compact('stats', 'recent'));
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

    public function create(): View
    {
        $this->authorize('create', PosSale::class);

        return view('admin.commercial.pos.create', $this->saleFormMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PosSale::class);

        $payload = $this->validateSalePayload($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $sale = $this->posSales->createSale(
            $payload,
            $companyId,
            $branchId,
            (int) auth()->id(),
        );

        if ($payload['action'] === 'hold') {
            return redirect()
                ->route('admin.commercial.pos.create')
                ->with('status', __('Sale held (:number).', ['number' => $sale->sale_number]));
        }

        return redirect()
            ->route('admin.commercial.pos.receipt', $sale)
            ->with('status', __('Sale saved.'));
    }

    public function show(PosSale $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['items.inventoryItem', 'payments', 'customer', 'cashier', 'hold']);

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

    public function resume(PosSale $sale): RedirectResponse
    {
        $this->authorize('update', $sale);

        abort_unless($sale->status === PosSaleStatus::Held, 404);

        return redirect()
            ->route('admin.commercial.pos.show', $sale)
            ->with('status', __('Resume held sale and complete checkout from the sale details.'));
    }

    public function cancel(PosSale $sale): RedirectResponse
    {
        $this->authorize('cancel', $sale);

        $sale->update(['status' => PosSaleStatus::Cancelled]);
        $sale->hold?->delete();

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
}
