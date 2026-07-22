<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Sales\SalesOrder;
use App\Support\Sales\SalesDeskPageBuilder;
use App\Support\Sales\SalesDeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesDeskController extends Controller
{
    public function __construct(
        protected SalesDeskPageBuilder $page,
        protected SalesDeskService $desk,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('create', Customer::class);
        $this->authorize('create', SalesOrder::class);

        return view('admin.sales.desk.index', $this->page->build($request));
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        return response()->json([
            'results' => $this->desk->searchCustomers((string) $request->query('q', '')),
        ]);
    }
}
