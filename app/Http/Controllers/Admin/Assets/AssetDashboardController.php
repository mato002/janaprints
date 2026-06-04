<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\FixedAssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\AssetMaintenance;
use App\Models\Assets\FixedAsset;
use App\Support\Assets\AssetDepreciationService;
use Illuminate\View\View;

class AssetDashboardController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth()->user()?->can('assets.view'), 403);

        $companyId = (int) tenant()->companyId();

        $stats = [
            'totals' => AssetDepreciationService::companyTotals($companyId),
            'by_category' => AssetCategory::query()
                ->where('company_id', $companyId)
                ->withCount('assets')
                ->get(),
            'due_service' => AssetMaintenance::query()
                ->where('status', 'scheduled')
                ->whereDate('scheduled_date', '<=', now()->addDays(30))
                ->count(),
            'under_repair' => FixedAsset::query()
                ->where('company_id', $companyId)
                ->where('status', FixedAssetStatus::UnderRepair)
                ->count(),
            'disposed' => FixedAsset::query()
                ->where('company_id', $companyId)
                ->where('status', FixedAssetStatus::Disposed)
                ->count(),
        ];

        return view('admin.assets.dashboard', compact('stats'));
    }
}
