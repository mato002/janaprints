<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Enums\CustomerStatus;
use App\Enums\FollowUpStatus;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Crm\LeadFollowUp;
use Illuminate\View\View;

class CrmDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', Customer::class);

        $customerQuery = Customer::query()->forTenant();
        $leadQuery = Lead::query()->forTenant();

        $stats = [
            'total_customers' => (clone $customerQuery)->count(),
            'active_customers' => (clone $customerQuery)->where('status', CustomerStatus::Active)->count(),
            'leads' => (clone $leadQuery)->count(),
            'open_opportunities' => (clone $leadQuery)->where('status', LeadStatus::Open)->count(),
            'follow_ups_due_today' => LeadFollowUp::query()
                ->forTenant()
                ->where('status', FollowUpStatus::Pending)
                ->whereDate('scheduled_at', today())
                ->count(),
        ];

        return view('admin.crm.dashboard', compact('stats'));
    }
}
