<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailCampaign;
use App\Models\Communications\EmailMessage;
use Illuminate\View\View;

class EmailDeliveryController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function index(): View
    {
        $this->authorize('audit', EmailCampaign::class);

        $messages = EmailMessage::query()
            ->forTenant()
            ->where('company_id', $this->requireCompanyId())
            ->with(['deliveryEvents', 'account'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.communications.email.delivery.index', compact('messages'));
    }

    public function show(EmailMessage $emailMessage): View
    {
        $this->authorize('audit', EmailCampaign::class);
        abort_unless($emailMessage->company_id === $this->requireCompanyId(), 404);

        $emailMessage->load(['deliveryEvents.creator', 'attachments', 'communicationLog', 'recipient']);

        return view('admin.communications.email.delivery.show', ['message' => $emailMessage]);
    }
}
