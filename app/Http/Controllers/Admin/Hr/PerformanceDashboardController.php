<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\PerformanceReview;
use App\Support\Hr\PerformanceReviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceDashboardController extends Controller
{
    public function __construct(
        protected PerformanceReviewService $reviews,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', PerformanceReview::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.performance.dashboard', [
            'stats' => $this->reviews->dashboardStats($companyId),
            'recentReviews' => PerformanceReview::query()
                ->forTenant()
                ->where('company_id', $companyId)
                ->with('employee')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'formData' => $this->reviews->formData($companyId),
        ]);
    }
}
