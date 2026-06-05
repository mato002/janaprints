<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Pos\PosSale;
use App\Support\Commercial\PosProductSearchService;
use App\Support\Commercial\PosSaleCalculator;
use App\Support\Commercial\PosSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosCounterSalesController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected PosProductSearchService $productSearch,
        protected PosSaleCalculator $calculator,
        protected PosSessionService $posSessions,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('counterSalesView', PosSale::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $activeSession = $this->posSessions->activeSessionForCashier(
            $companyId,
            $branchId,
            (int) $request->user()->id,
        );

        return view('admin.commercial.pos.counter-sales', [
            'activeSession' => $activeSession,
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']),
            'searchUrl' => route('admin.commercial.pos.counter-sales.products.search'),
            'storeUrl' => route('admin.commercial.pos.store'),
            'dashboardUrl' => route('admin.commercial.pos.dashboard'),
            'customerCreateUrl' => route('admin.crm.customers.create'),
            'previewTotals' => $this->calculator->totals([]),
            'canHold' => $request->user()->can('hold', PosSale::class),
            'canComplete' => $request->user()->can('completeSale', PosSale::class),
            'canCancel' => $request->user()->can('cancelSale', PosSale::class),
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
}
