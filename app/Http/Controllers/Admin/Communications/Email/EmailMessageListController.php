<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailAccountService;
use App\Support\Communications\Email\EmailMessageService;
use App\Support\Communications\Email\EmailVisibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class EmailMessageListController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailMessageService $messages,
        protected EmailAccountService $accounts,
        protected EmailVisibilityService $visibility,
    ) {}

    abstract protected function viewName(): string;

    abstract protected function viewMode(): string;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmailCampaign::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['status', 'sender', 'module', 'date_from', 'date_to', 'q']);

        $messages = $this->messages
            ->query($companyId, array_merge($filters, ['view' => $this->viewMode()]))
            ->paginate(25)
            ->withQueryString();

        return view($this->viewName(), [
            'messages' => $messages,
            'filters' => $filters,
            'accounts' => $this->accounts->listForCompany($companyId),
            'viewMode' => $this->viewMode(),
            'mailbox' => $this->visibility->mailboxSummary($companyId),
            'activeFolder' => $this->viewMode() === 'inbox' ? 'needs_attention' : $this->viewMode(),
        ]);
    }
}
