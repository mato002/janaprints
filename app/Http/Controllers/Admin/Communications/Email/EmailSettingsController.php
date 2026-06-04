<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailAccountService;
use Illuminate\View\View;

class EmailSettingsController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailAccountService $accounts,
    ) {}

    public function index(): View
    {
        $this->authorize('manage', EmailCampaign::class);

        return view('admin.communications.email.settings', [
            'accounts' => $this->accounts->listForCompany($this->requireCompanyId()),
        ]);
    }
}
