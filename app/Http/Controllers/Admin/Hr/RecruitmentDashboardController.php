<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\JobApplication;
use App\Support\Hr\RecruitmentDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecruitmentDashboardController extends Controller
{
    public function __construct(
        protected RecruitmentDashboardService $dashboard,
    ) {}

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', JobApplication::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.recruitment.dashboard', [
            'stats' => $this->dashboard->stats($companyId),
            'pipeline' => $this->dashboard->pipelineCounts($companyId),
            'recentApplications' => $this->dashboard->recentApplications($companyId),
            'formData' => $this->dashboard->formData($companyId),
        ]);
    }
}
