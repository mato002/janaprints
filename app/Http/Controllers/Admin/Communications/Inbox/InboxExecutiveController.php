<?php

namespace App\Http\Controllers\Admin\Communications\Inbox;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Support\Communications\Inbox\InboxExecutiveService;
use Illuminate\View\View;

class InboxExecutiveController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected InboxExecutiveService $executive,
    ) {}

    public function index(): View
    {
        $this->authorize('executive', CommunicationConversation::class);

        return view('admin.communications.inbox.executive', [
            'stats' => $this->executive->dashboard($this->requireCompanyId()),
        ]);
    }
}
