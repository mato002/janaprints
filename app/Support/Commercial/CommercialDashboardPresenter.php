<?php

namespace App\Support\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Enums\PosSaleStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Pos\PosSale;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;

class CommercialDashboardPresenter
{
    /**
     * @return array<string, int|float|string>
     */
    public function widgets(): array
    {
        $leadBase = Lead::query()->forTenant();
        $customerBase = Customer::query()->forTenant();

        $widgets = [
            'new_leads_today' => (clone $leadBase)->whereDate('created_at', today())->count(),
            'open_leads' => (clone $leadBase)->where('status', LeadStatus::Open)->count(),
            'active_customers' => (clone $customerBase)->where('status', CustomerStatus::Active)->count(),
            'quotes_pending_approval' => Quotation::query()->forTenant()
                ->where('status', QuotationStatus::PendingApproval)
                ->count(),
            'artwork_awaiting_approval' => ArtworkRequest::query()->forTenant()
                ->whereIn('status', [
                    ArtworkRequestStatus::Submitted,
                    ArtworkRequestStatus::RevisionRequested,
                ])
                ->count(),
            'sales_orders_ready' => SalesOrder::query()->forTenant()
                ->where('status', SalesOrderStatus::ReadyForProduction)
                ->count(),
        ];

        if (auth()->user()?->can('pos.view')) {
            $widgets['pos_sales_today'] = PosSale::query()->forTenant()
                ->whereDate('sale_date', today())
                ->where('status', PosSaleStatus::Paid)
                ->count();
        }

        return $widgets;
    }
}
