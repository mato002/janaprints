<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\Sales\Quotation;
use Illuminate\View\View;

class QuotationDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', Quotation::class);

        $base = Quotation::query()->forTenant();
        $sent = (clone $base)->where('status', QuotationStatus::Sent)->count();
        $accepted = (clone $base)->where('status', QuotationStatus::Accepted)->count();
        $converted = (clone $base)->where('status', QuotationStatus::Converted)->count();
        $totalClosed = $accepted + $converted;

        $stats = [
            'draft' => (clone $base)->where('status', QuotationStatus::Draft)->count(),
            'pending_approval' => (clone $base)->where('status', QuotationStatus::PendingApproval)->count(),
            'sent' => $sent,
            'accepted' => $accepted,
            'conversion_rate' => $sent > 0 ? round(($totalClosed / $sent) * 100, 1) : 0,
        ];

        return view('admin.sales.quotations.dashboard', compact('stats'));
    }
}
