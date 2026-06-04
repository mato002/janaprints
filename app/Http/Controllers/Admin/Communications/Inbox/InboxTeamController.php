<?php

namespace App\Http\Controllers\Admin\Communications\Inbox;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Support\Communications\Inbox\InboxTeamPerformanceService;
use Illuminate\View\View;

class InboxTeamController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected InboxTeamPerformanceService $performance,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', CommunicationConversation::class);

        return view('admin.communications.inbox.team', [
            'report' => $this->performance->report($this->requireCompanyId()),
        ]);
    }
}
