<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Support\Communications\Email\EmailAnalyticsService;
use Illuminate\View\View;

class EmailDashboardController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailAnalyticsService $analytics,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\Communications\EmailCampaign::class);

        return view('admin.communications.email.dashboard', [
            'stats' => $this->analytics->dashboard($this->requireCompanyId()),
        ]);
    }
}
