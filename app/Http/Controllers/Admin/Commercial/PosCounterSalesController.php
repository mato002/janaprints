<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\PosSaleStatus;
use App\Enums\PosSessionStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Pos\PosSale;
use App\Models\Pos\PosSaleHold;
use App\Models\Pos\PosSession;
use App\Models\User;
use App\Support\Commercial\PosCashReconciliationService;
use App\Support\Commercial\PosCounterSalesPresenter;
use App\Support\Commercial\PosProductSearchService;
use App\Support\Commercial\PosSaleCalculator;
use App\Support\Commercial\PosSessionService;
use App\Support\Commercial\PosSessionVarianceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PosCounterSalesController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected PosProductSearchService $productSearch,
        protected PosSaleCalculator $calculator,
        protected PosSessionService $posSessions,
        protected PosCounterSalesPresenter $presenter,
        protected PosSessionVarianceService $variance,
        protected PosCashReconciliationService $reconciliations,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('counterSalesView', PosSale::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);
        $user = $request->user();

        $activeSession = $this->posSessions->activeSessionForCashier(
            $companyId,
            $branchId,
            (int) $user->id,
        );

        $sessionWidget = $this->presenter->sessionWidget($activeSession);

        $cashiers = $user->can('open', PosSession::class)
            ? User::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('admin.commercial.pos.counter-sales', [
            'workstationConfig' => [
                'csrf' => csrf_token(),
                'resumeSaleId' => $request->integer('resume') ?: null,
                'resumeFetchUrl' => $request->integer('resume')
                    ? route('admin.commercial.pos.counter-sales.held-sales.resume', $request->integer('resume'))
                    : null,
                'session' => $sessionWidget,
                'urls' => $this->workstationUrls(),
                'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']),
                'cashiers' => $cashiers,
                'defaultCashierId' => (int) $user->id,
                'defaultTerminal' => config('pos.default_terminal'),
                'varianceTolerance' => $this->variance->tolerance(),
                'permissions' => [
                    'canHold' => $user->can('hold', PosSale::class),
                    'canComplete' => $user->can('completeSale', PosSale::class),
                    'canCancel' => $user->can('cancelSale', PosSale::class),
                    'canOpenSession' => $user->can('open', PosSession::class),
                    'canCloseSession' => $activeSession && $user->can('close', $activeSession),
                    'canReprint' => $user->can('pos.receipts.reprint'),
                    'canAddCustomer' => $user->can('create', Customer::class),
                ],
                'customerCreateUrl' => route('admin.crm.customers.create'),
                'dashboardUrl' => route('admin.commercial.pos.dashboard'),
            ],
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $this->authorize('counterSalesView', PosSale::class);

        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $customerId = $request->filled('customer_id') ? $request->integer('customer_id') : null;

        if ($request->filled('barcode')) {
            $match = $this->productSearch->findByBarcode($request->string('barcode')->toString(), $customerId);

            return response()->json([
                'products' => $match ? [$match] : [],
                'exact' => $match !== null,
            ]);
        }

        $products = $this->productSearch->search($request->string('q')->toString(), $customerId);

        return response()->json([
            'products' => $products->values()->all(),
            'exact' => false,
        ]);
    }

    public function sessionState(Request $request): JsonResponse
    {
        $this->authorize('counterSalesView', PosSale::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $session = $this->posSessions->activeSessionForCashier(
            $companyId,
            $branchId,
            (int) $request->user()->id,
        );

        $widget = $this->presenter->sessionWidget($session);
        $widget['permissions'] = [
            'canCloseSession' => $session && $request->user()->can('close', $session),
        ];

        return response()->json($widget);
    }

    public function openSession(Request $request): JsonResponse
    {
        $this->authorize('open', PosSession::class);

        $data = $request->validate([
            'cashier_id' => ['required', 'integer', 'exists:users,id'],
            'opening_float' => ['required', 'numeric', 'min:0'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
            'terminal' => ['nullable', 'string', 'max:40'],
            'opening_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $session = $this->posSessions->openSession(
            companyId: $companyId,
            branchId: $branchId,
            cashierId: (int) $data['cashier_id'],
            openingFloat: (float) $data['opening_float'],
            openingCash: (float) $data['opening_cash'],
            openedBy: (int) $request->user()->id,
            notes: $data['opening_notes'] ?? null,
            terminal: $data['terminal'] ?? null,
        );

        return response()->json([
            'message' => __('POS session :number opened.', ['number' => $session->session_number]),
            'session' => $this->presenter->sessionWidget($session),
        ]);
    }

    public function closePreview(Request $request): JsonResponse
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $session = $this->posSessions->activeSessionForCashier(
            $companyId,
            $branchId,
            (int) $request->user()->id,
        );

        abort_unless($session !== null, 404);
        $this->authorize('close', $session);
        abort_unless($session->status === PosSessionStatus::Open, 422);

        return response()->json($this->presenter->closePreview($session));
    }

    public function closeSession(Request $request): JsonResponse
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $session = $this->posSessions->activeSessionForCashier(
            $companyId,
            $branchId,
            (int) $request->user()->id,
        );

        abort_unless($session !== null, 404);
        $this->authorize('close', $session);

        $data = $request->validate([
            'actual_cash' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $closed = $this->posSessions->closeSession(
            session: $session,
            actualCash: (float) $data['actual_cash'],
            closedBy: (int) $request->user()->id,
            notes: $data['closing_notes'] ?? null,
        );

        if ($closed->status === PosSessionStatus::Closed && Schema::hasTable('pos_cash_reconciliations')) {
            $this->reconciliations->createFromSession($closed, (int) $request->user()->id);
        }

        return response()->json([
            'message' => $closed->status === PosSessionStatus::PendingApproval
                ? __('Session closed — variance pending manager approval.')
                : __('POS session :number closed.', ['number' => $closed->session_number]),
            'session' => $this->presenter->sessionWidget(null),
            'closed' => [
                'status' => $closed->status->value,
                'variance' => (float) $closed->variance,
                'requires_approval' => $closed->variance_requires_approval,
            ],
        ]);
    }

    public function heldSales(Request $request): JsonResponse
    {
        $this->authorize('counterSalesView', PosSale::class);

        $holds = $this->scopeToTenant(
            PosSaleHold::query()
                ->with(['sale', 'customer:id,company_name', 'cashier:id,name'])
        )
            ->whereHas('sale', fn ($q) => $q->where('status', PosSaleStatus::Held))
            ->latest('held_at')
            ->limit(50)
            ->get();

        return response()->json([
            'holds' => $this->presenter->heldSalesList($holds),
        ]);
    }

    public function resumeHeldSale(PosSale $sale): JsonResponse
    {
        abort_unless(
            auth()->user()->can('complete', $sale) || auth()->user()->can('update', $sale),
            403,
        );
        abort_unless($sale->status === PosSaleStatus::Held, 404);

        return response()->json([
            'cart' => $this->presenter->heldCartPayload($sale),
        ]);
    }

    public function receiptPayload(PosSale $sale): JsonResponse
    {
        $this->authorize('view', $sale);

        return response()->json([
            'receipt' => $this->presenter->receiptPayload($sale),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function workstationUrls(): array
    {
        return [
            'search' => route('admin.commercial.pos.counter-sales.products.search'),
            'store' => route('admin.commercial.pos.store'),
            'session' => route('admin.commercial.pos.counter-sales.session'),
            'openSession' => route('admin.commercial.pos.counter-sales.session.open'),
            'closePreview' => route('admin.commercial.pos.counter-sales.session.close-preview'),
            'closeSession' => route('admin.commercial.pos.counter-sales.session.close'),
            'heldSales' => route('admin.commercial.pos.counter-sales.held-sales'),
        ];
    }
}
