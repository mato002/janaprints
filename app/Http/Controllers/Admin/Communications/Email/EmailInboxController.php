<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Support\Communications\Email\EmailMessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailInboxController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailMessageService $messages,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmailCampaign::class);

        $messages = $this->messages
            ->query($this->requireCompanyId(), array_merge($request->only('q'), ['view' => 'inbox']))
            ->paginate(25);

        return view('admin.communications.email.inbox', compact('messages'));
    }
}
