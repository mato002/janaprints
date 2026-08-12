<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailAutomationMapper;
use App\Support\Communications\Email\EmailTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailTemplateService $templates,
        protected EmailAutomationMapper $automation,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', EmailCampaign::class);

        $companyId = $this->requireCompanyId();

        return view('admin.communications.email.templates.index', [
            'bindings' => $this->templates->listBindings($companyId),
            'automationMap' => $this->automation->mapForCompany($companyId),
            'mailbox' => app(\App\Support\Communications\Email\EmailVisibilityService::class)->mailboxSummary($companyId),
            'activeFolder' => 'templates',
            'filters' => [],
        ]);
    }

    public function sync(): RedirectResponse
    {
        $this->authorize('manage', EmailCampaign::class);

        $count = $this->templates->syncFromCom1($this->requireCompanyId());

        return back()->with('status', __(':count email template binding(s) synced.', ['count' => $count]));
    }
}
