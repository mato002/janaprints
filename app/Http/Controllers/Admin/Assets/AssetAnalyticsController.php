<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Services\Assets\AssetAnalyticsService;
use Illuminate\View\View;

class AssetAnalyticsController extends Controller
{
    public function __construct(
        protected AssetAnalyticsService $analytics,
    ) {}

    public function __invoke(): View
    {
        abort_unless(auth()->user()?->can('assets.analytics.view'), 403);

        return view('admin.assets.intelligence.analytics', [
            'stats' => $this->analytics->build(
                (int) tenant()->companyId(),
                tenant()->branchId(),
            ),
        ]);
    }
}
