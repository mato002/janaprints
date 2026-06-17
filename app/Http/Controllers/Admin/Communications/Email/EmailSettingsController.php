<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailAccountService;
use App\Support\Communications\Email\EmailDeliveryDiagnosticsService;
use Illuminate\View\View;

class EmailSettingsController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailAccountService $accounts,
        protected EmailDeliveryDiagnosticsService $diagnostics,
    ) {}

    public function index(): View
    {
        $this->authorize('manage', EmailCampaign::class);

        $companyId = $this->requireCompanyId();

        return view('admin.communications.email.settings', [
            'accounts' => $this->accounts->listForCompany($companyId),
            'diagnostics' => $this->diagnostics->forCompany($companyId),
        ]);
    }
}
