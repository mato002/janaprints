<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailAnalyticsService;
use Illuminate\View\View;

class EmailAnalyticsController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailAnalyticsService $analytics,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', EmailCampaign::class);

        return view('admin.communications.email.analytics', [
            'stats' => $this->analytics->dashboard($this->requireCompanyId()),
        ]);
    }
}
