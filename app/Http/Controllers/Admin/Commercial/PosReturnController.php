<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\PosRefundMethod;
use App\Enums\PosReturnStatus;
use App\Enums\PosReturnType;
use App\Enums\PosSaleStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Pos\PosReturn;
use App\Models\Pos\PosSale;
use App\Support\Commercial\PosReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosReturnController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected PosReturnService $returns,
    ) {}

    public function dashboard(Request $request): View
    {
        $this->authorize('viewAny', PosReturn::class);

        $base = $this->scopeToTenant(PosReturn::query());
        $today = (clone $base)->whereDate('created_at', today());

        $stats = [
            'pending' => (clone $base)->where('status', PosReturnStatus::Pending)->count(),
            'completed_today' => (clone $today)->where('status', PosReturnStatus::Completed)->count(),
            'refund_today' => (clone $today)->where('status', PosReturnStatus::Completed)->sum('refund_amount'),
            'rejected' => (clone $base)->where('status', PosReturnStatus::Rejected)->count(),
        ];

        $recent = (clone $base)
            ->with(['sale:id,sale_number', 'creator:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.commercial.pos.returns.dashboard', compact('stats', 'recent'));
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PosReturn::class);

        $returns = $this->scopeToTenant(
            PosReturn::query()->with(['sale:id,sale_number', 'creator:id,name', 'approver:id,name'])
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('return_type'), fn ($q) => $q->where('return_type', $request->string('return_type')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->date('date')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.pos.returns.index', [
            'returns' => $returns,
            'filters' => $request->only(['status', 'return_type', 'date']),
            'returnTypes' => PosReturnType::cases(),
            'statuses' => PosReturnStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PosReturn::class);

        $sale = null;
        $returnableItems = collect();

        if ($request->filled('sale')) {
            $sale = $this->scopeToTenant(PosSale::query())
                ->with(['items', 'customer:id,company_name', 'cashier:id,name'])
                ->whereIn('status', [PosSaleStatus::Paid, PosSaleStatus::PartiallyRefunded])
                ->where(function ($q) use ($request) {
                    $value = $request->string('sale');
                    $q->where('sale_number', $value)->orWhere('id', $value);
                })
                ->first();

            if ($sale !== null) {
                $returnableItems = $sale->items->map(fn ($item) => [
                    'item' => $item,
                    'returnable_qty' => $this->returns->returnableQuantity($item),
                ])->filter(fn ($row) => $row['returnable_qty'] > 0);
            }
        }

        return view('admin.commercial.pos.returns.create', [
            'sale' => $sale,
            'returnableItems' => $returnableItems,
            'returnTypes' => PosReturnType::cases(),
            'refundMethods' => PosRefundMethod::cases(),
            'search' => $request->string('sale')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PosReturn::class);

        $validated = $request->validate([
            'sale_number' => ['required', 'string', 'max:40'],
            'return_type' => ['required', Rule::enum(PosReturnType::class)],
            'refund_method' => ['required', Rule::enum(PosRefundMethod::class)],
            'reason' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['nullable', 'array'],
            'lines.*.pos_sale_item_id' => ['required_with:lines', 'integer'],
            'lines.*.quantity_returned' => ['required_with:lines', 'numeric', 'min:0.001'],
            'lines.*.reason' => ['nullable', 'string', 'max:500'],
        ]);

        $sale = $this->scopeToTenant(PosSale::query())
            ->where('sale_number', $validated['sale_number'])
            ->whereIn('status', [PosSaleStatus::Paid, PosSaleStatus::PartiallyRefunded])
            ->firstOrFail();

        $return = $this->returns->createReturn(
            $sale,
            PosReturnType::from($validated['return_type']),
            PosRefundMethod::from($validated['refund_method']),
            $validated['reason'],
            $validated['lines'] ?? [],
            (int) $request->user()->id,
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('admin.commercial.pos.returns.show', $return)
            ->with('status', __('Return :number submitted for approval.', ['number' => $return->return_number]));
    }

    public function show(PosReturn $return): View
    {
        $this->authorize('view', $return);

        $return->load([
            'sale.items',
            'sale.customer:id,company_name',
            'sale.cashier:id,name',
            'items.saleItem',
            'creator:id,name',
            'approver:id,name',
            'events.actor:id,name',
        ]);

        return view('admin.commercial.pos.returns.show', [
            'return' => $return,
            'canApprove' => request()->user()?->can('approve', $return) && $return->status === PosReturnStatus::Pending,
            'canAudit' => request()->user()?->can('audit', $return),
        ]);
    }

    public function approve(Request $request, PosReturn $return): RedirectResponse
    {
        $this->authorize('approve', $return);

        $this->returns->approveReturn($return, (int) $request->user()->id);

        return back()->with('status', __('Return :number approved and completed.', ['number' => $return->return_number]));
    }

    public function reject(Request $request, PosReturn $return): RedirectResponse
    {
        $this->authorize('reject', $return);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->returns->rejectReturn($return, (int) $request->user()->id, $validated['rejection_reason']);

        return back()->with('status', __('Return :number rejected.', ['number' => $return->return_number]));
    }
}
