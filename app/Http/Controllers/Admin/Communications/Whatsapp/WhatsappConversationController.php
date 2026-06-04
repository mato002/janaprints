<?php

namespace App\Http\Controllers\Admin\Communications\Whatsapp;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappTemplate;
use App\Support\Communications\Whatsapp\WhatsappConversationService;
use App\Support\Communications\Whatsapp\WhatsappMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsappConversationController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected WhatsappConversationService $conversations,
        protected WhatsappMessageService $messages,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WhatsappConversation::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['q', 'status', 'assigned_user_id']);
        $conversations = $this->conversations->query($companyId, $filters)->paginate(25)->withQueryString();

        return view('admin.communications.whatsapp.conversations.index', compact('conversations', 'filters'));
    }

    public function show(WhatsappConversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $conversation->load([
            'messages.creator', 'messages.deliveryEvents', 'messages.communicationTemplate',
            'participants', 'customer', 'account', 'assignee',
        ]);
        $this->conversations->markRead($conversation);

        $templates = WhatsappTemplate::query()
            ->where('company_id', $conversation->company_id)
            ->where('is_active', true)
            ->with('communicationTemplate')
            ->get();

        return view('admin.communications.whatsapp.conversations.show', compact('conversation', 'templates'));
    }

    public function storeMessage(Request $request, WhatsappConversation $conversation): RedirectResponse
    {
        $this->authorize('send', WhatsappConversation::class);
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'body' => ['required_without:whatsapp_template_id', 'string', 'max:4096'],
            'whatsapp_template_id' => ['nullable', 'exists:whatsapp_templates,id'],
        ]);

        if (! empty($validated['whatsapp_template_id'])) {
            $template = WhatsappTemplate::query()
                ->where('company_id', $conversation->company_id)
                ->findOrFail($validated['whatsapp_template_id']);
            $this->messages->sendTemplate($conversation, $template, $request->user()->id);
        } else {
            $this->messages->sendManual($conversation, $validated['body'], $request->user()->id);
        }

        return redirect()
            ->route('admin.communications.whatsapp.conversations.show', $conversation)
            ->with('status', __('Message queued.'));
    }

    public function update(Request $request, WhatsappConversation $conversation): RedirectResponse
    {
        $this->authorize('manage', WhatsappConversation::class);
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'array'],
        ]);

        $conversation->update(array_filter([
            'status' => $validated['status'] ?? null,
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'tags' => $validated['tags'] ?? null,
        ], fn ($v) => $v !== null));

        return back()->with('status', __('Conversation updated.'));
    }
}
