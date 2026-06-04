<?php

namespace App\Http\Controllers\Admin\Communications\Whatsapp;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Communications\WhatsappConversation;
use App\Support\Communications\Whatsapp\WhatsappAnalyticsService;
use App\Support\Communications\Whatsapp\WhatsappConversationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappInboxController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected WhatsappConversationService $conversations,
        protected WhatsappAnalyticsService $analytics,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WhatsappConversation::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['q', 'status', 'assigned_user_id', 'branch_id', 'unread_only']);
        $conversations = $this->conversations->query($companyId, $filters)->paginate(20)->withQueryString();
        $stats = $this->analytics->dashboard($companyId);
        $branches = Branch::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('admin.communications.whatsapp.inbox', compact('conversations', 'filters', 'stats', 'branches'));
    }
}
