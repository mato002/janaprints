<?php

namespace App\Http\Controllers\Admin\Communications\Whatsapp;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappMessage;
use App\Support\Communications\Whatsapp\WhatsappMessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappDeliveryController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected WhatsappMessageService $messages,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('audit', WhatsappConversation::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['status', 'q']);
        $messages = $this->messages->query($companyId, $filters)->paginate(25)->withQueryString();

        return view('admin.communications.whatsapp.delivery.index', compact('messages', 'filters'));
    }

    public function show(WhatsappMessage $message): View
    {
        $this->authorize('audit', WhatsappConversation::class);

        $message->load(['deliveryEvents.creator', 'conversation', 'account', 'communicationTemplate', 'communicationLog']);

        return view('admin.communications.whatsapp.delivery.show', compact('message'));
    }
}
