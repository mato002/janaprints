<?php

namespace App\Http\Controllers\Admin\Communications\Sms;

use App\Http\Controllers\Admin\Communications\Sms\Concerns\ResolvesSmsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\SmsCampaign;
use App\Support\Communications\Sms\SmsCrmWalletTopupService;
use App\Support\Communications\Sms\SmsDashboardPresenter;
use Illuminate\View\View;

class SmsDashboardController extends Controller
{
    use ResolvesSmsTenant;

    public function __construct(
        protected SmsDashboardPresenter $dashboard,
        protected SmsCrmWalletTopupService $topups,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SmsCampaign::class);

        $companyId = $this->requireCompanyId();
        $stats = $this->dashboard->build($companyId);
        $topupConfig = $this->topups->uiConfig();

        return view('admin.communications.sms.dashboard', compact('stats', 'topupConfig'));
    }
}
