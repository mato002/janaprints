<?php

namespace App\Http\Controllers\Admin\Communications\Whatsapp;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\WhatsappConversation;
use App\Support\Communications\Whatsapp\WhatsappMessageService;
use Illuminate\View\View;

class WhatsappQueueController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected WhatsappMessageService $messages,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', WhatsappConversation::class);

        $messages = $this->messages
            ->query($this->requireCompanyId(), ['view' => 'queue'])
            ->paginate(25);

        return view('admin.communications.whatsapp.queue.index', compact('messages'));
    }
}
