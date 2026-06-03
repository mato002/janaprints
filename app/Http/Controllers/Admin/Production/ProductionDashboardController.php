<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionJobCardStatus;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use Illuminate\View\View;

class ProductionDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', ProductionJobCard::class);

        $base = ProductionJobCard::query()->forTenant();
        $today = now()->toDateString();

        $stats = [
            'open' => (clone $base)->whereIn('status', [
                ProductionJobCardStatus::Draft,
                ProductionJobCardStatus::Queued,
            ])->count(),
            'in_production' => (clone $base)->where('status', ProductionJobCardStatus::InProduction)->count(),
            'awaiting_qc' => (clone $base)->where('status', ProductionJobCardStatus::QualityCheck)->count(),
            'completed_today' => (clone $base)->where('status', ProductionJobCardStatus::Completed)
                ->whereDate('updated_at', $today)->count(),
            'delayed' => (clone $base)->whereNotIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Cancelled,
            ])->whereDate('planned_end_date', '<', $today)->count(),
        ];

        return view('admin.production.dashboard', compact('stats'));
    }
}
