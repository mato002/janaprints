<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use Illuminate\View\View;

class SalesOrderDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $base = SalesOrder::query()->forTenant();

        $stats = [
            'draft' => (clone $base)->where('status', SalesOrderStatus::Draft)->count(),
            'confirmed' => (clone $base)->where('status', SalesOrderStatus::Confirmed)->count(),
            'ready_for_production' => (clone $base)->where('status', SalesOrderStatus::ReadyForProduction)->count(),
            'in_production' => (clone $base)->where('status', SalesOrderStatus::InProduction)->count(),
            'completed' => (clone $base)->where('status', SalesOrderStatus::Completed)->count(),
            'delivered' => (clone $base)->where('status', SalesOrderStatus::Delivered)->count(),
        ];

        return view('admin.sales.orders.dashboard', compact('stats'));
    }
}
