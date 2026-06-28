<?php

namespace App\Http\Controllers\Admin\Communications\Inbox;

use App\Enums\InboxAuditEventType;
use App\Enums\InboxConversationStatus;
use App\Enums\InboxMessageChannel;
use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\Communications\Inbox\CommunicationConversationAttachment;
use App\Models\Communications\Inbox\CommunicationConversationMessage;
use App\Models\Department;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Communications\Inbox\InboxAssignmentService;
use App\Support\Communications\Inbox\InboxAuditService;
use App\Support\Communications\Inbox\InboxConversationService;
use App\Support\Communications\Inbox\InboxCustomerContextService;
use App\Support\Communications\Inbox\InboxMessageService;
use App\Support\Communications\Inbox\InboxNoteService;
use App\Support\Communications\Inbox\InboxSlaService;
use App\Support\Communications\Inbox\InboxChatFeedService;
use App\Support\Communications\Inbox\InboxConversationWorkspaceService;
use App\Support\Communications\Inbox\InboxTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InboxController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected InboxConversationService $conversations,
        protected InboxMessageService $messages,
        protected InboxNoteService $notes,
        protected InboxAssignmentService $assignments,
        protected InboxCustomerContextService $customerContext,
        protected InboxTimelineService $timeline,
        protected InboxConversationWorkspaceService $workspace,
        protected InboxSlaService $sla,
        protected InboxAuditService $audit,
        protected InboxChatFeedService $chatFeed,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommunicationConversation::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['view', 'q', 'assigned_user_id', 'conversation_type', 'status', 'tag', 'pick_q', 'channel']);
        $conversations = $this->conversations->query($companyId, $filters)->paginate(30)->withQueryString();

        $pickQ = trim((string) ($filters['pick_q'] ?? ''));
        $pickCustomers = Customer::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->when($pickQ !== '', function ($query) use ($pickQ) {
                $query->where(function ($inner) use ($pickQ) {
                    $inner->where('company_name', 'like', "%{$pickQ}%")
                        ->orWhere('customer_code', 'like', "%{$pickQ}%")
                        ->orWhere('phone', 'like', "%{$pickQ}%")
                        ->orWhere('contact_person', 'like', "%{$pickQ}%");
                });
            })
            ->orderBy('company_name')
            ->limit(50)
            ->get(['id', 'company_name', 'customer_code', 'phone']);

        $active = null;
        $context = null;
        $workspaceData = null;
        $channelFilter = $request->get('channel');
        $watchers = collect();

        if ($id = $request->integer('conversation')) {
            $active = CommunicationConversation::query()
                ->forTenant()
                ->where('company_id', $companyId)
                ->with([
                    'customer.branch', 'branch', 'assignee', 'owner', 'assignedDepartment',
                    'threadMessages.creator', 'notes.author', 'attachments.uploader',
                    'assignments.fromUser', 'assignments.toUser', 'assignments.creator',
                    'statusHistory.creator', 'auditEvents.user',
                ])
                ->find($id);
            if ($active) {
                $this->authorize('view', $active);
                $this->conversations->markRead($active);
                $this->sla->refresh($active);
                $context = $this->customerContext->forConversation($active);
                $workspaceData = $this->workspace->forConversation($active, $channelFilter);
                $watcherIds = $active->watcher_user_ids ?? [];
                $watchers = $watcherIds
                    ? User::query()->whereIn('id', $watcherIds)->get(['id', 'name'])
                    : collect();
            }
        }

        $staff = User::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);
        $departments = Department::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);
        $branches = Branch::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('admin.communications.inbox.index', compact(
            'conversations', 'filters', 'active', 'staff', 'departments', 'branches',
            'context', 'workspaceData', 'channelFilter', 'watchers', 'pickCustomers', 'pickQ',
        ));
    }

    public function updateTags(Request $request, CommunicationConversation $inboxConversation): RedirectResponse
    {
        $this->authorize('view', $inboxConversation);

        $raw = $request->validate(['tags' => ['nullable', 'string', 'max:500']])['tags'] ?? '';
        $tags = array_values(array_unique(array_filter(array_map(
            fn ($t) => strtolower(preg_replace('/[^a-z0-9_-]/i', '', $t) ?? ''),
            preg_split('/[\s,]+/', $raw) ?: [],
        ))));

        $inboxConversation->update(['tags' => $tags ?: null]);
        $this->audit->record($inboxConversation, InboxAuditEventType::RecordLinked, $request->user()->id, [
            'summary' => __('Tags updated: ').implode(', ', $tags),
        ]);

        return redirect()->route('admin.communications.inbox.index', [
            'conversation' => $inboxConversation->id,
            'channel' => $request->get('channel'),
        ]);
    }

    public function startFromPicker(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', CommunicationConversation::class);

        $customer = Customer::query()
            ->forTenant()
            ->where('company_id', $this->requireCompanyId())
            ->findOrFail($request->validate([
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
            ])['customer_id']);

        return $this->startCustomer($request, $customer);
    }

    public function startCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('viewAny', CommunicationConversation::class);

        abort_unless($customer->company_id === $this->requireCompanyId(), 404);

        $conversation = $this->conversations->findOrCreateForCustomer($customer, $request->user()->id);

        return redirect()->route('admin.communications.inbox.index', [
            'conversation' => $conversation->id,
            'view' => $request->get('view', 'customer'),
        ]);
    }

    public function reply(Request $request, CommunicationConversation $inboxConversation): RedirectResponse
    {
        $this->authorize('reply', CommunicationConversation::class);
        $this->authorize('view', $inboxConversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:8000'],
            'channel' => ['nullable', 'string'],
        ], [], ['body' => __('message')]);

        $channel = InboxMessageChannel::tryFrom($validated['channel'] ?? '') ?? InboxMessageChannel::InApp;
        $this->messages->reply($inboxConversation, $validated['body'], $request->user()->id, $channel);

        return $this->redirectToConversation($inboxConversation, $request)
            ->with('inbox_reply_sent', true);
    }

    public function storeNote(Request $request, CommunicationConversation $inboxConversation): RedirectResponse
    {
        $this->authorize('notes', CommunicationConversation::class);
        $this->authorize('view', $inboxConversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:8000'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);
        $tags = array_filter(array_map('trim', explode(',', $validated['tags'] ?? '')));
        $this->notes->add($inboxConversation, $validated['body'], $request->user()->id, tags: $tags);

        return $this->redirectToConversation($inboxConversation, $request);
    }

    public function assign(Request $request, CommunicationConversation $inboxConversation): RedirectResponse
    {
        $this->authorize('assign', CommunicationConversation::class);
        $this->authorize('view', $inboxConversation);

        $validated = $request->validate([
            'assigned_user_id' => ['nullable', 'required_if:action,assign', 'exists:users,id'],
            'assigned_department_id' => ['nullable', 'exists:departments,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'watcher_user_id' => ['nullable', 'exists:users,id'],
            'action' => ['required', 'in:assign,take,release,escalate,add_watcher,remove_watcher,assign_department,assign_branch'],
        ]);

        if ($validated['action'] === 'escalate') {
            $this->authorize('escalate', CommunicationConversation::class);
        }

        match ($validated['action']) {
            'take' => $this->assignments->takeOwnership($inboxConversation, $request->user()->id),
            'release' => $this->assignments->release($inboxConversation, $request->user()->id),
            'escalate' => $this->assignments->escalate($inboxConversation, $request->user()->id),
            'add_watcher' => $this->assignments->addWatcher(
                $inboxConversation,
                (int) $validated['watcher_user_id'],
                $request->user()->id,
            ),
            'remove_watcher' => $this->assignments->removeWatcher(
                $inboxConversation,
                (int) $validated['watcher_user_id'],
                $request->user()->id,
            ),
            'assign_department' => $this->assignments->assignDepartment(
                $inboxConversation,
                $validated['assigned_department_id'] ?? null,
                $request->user()->id,
            ),
            'assign_branch' => $this->assignments->assignBranch(
                $inboxConversation,
                $validated['branch_id'] ?? null,
                $request->user()->id,
            ),
            default => $this->assignments->assign(
                $inboxConversation,
                (int) $validated['assigned_user_id'],
                $request->user()->id,
            ),
        };

        return redirect()->route('admin.communications.inbox.index', ['conversation' => $inboxConversation->id]);
    }

    public function updateStatus(Request $request, CommunicationConversation $inboxConversation): RedirectResponse
    {
        $this->authorize('close', CommunicationConversation::class);

        if ($request->input('status') === 'reopen') {
            $this->assignments->reopen($inboxConversation, $request->user()->id);
        } elseif ($status = InboxConversationStatus::tryFrom($request->input('status', ''))) {
            $this->assignments->setStatus($inboxConversation, $status, $request->user()->id);
        } else {
            $this->assignments->close($inboxConversation, $request->user()->id);
        }

        return redirect()->route('admin.communications.inbox.index', ['conversation' => $inboxConversation->id]);
    }

    public function storeAttachment(Request $request, CommunicationConversation $inboxConversation): RedirectResponse
    {
        $this->authorize('attachments', CommunicationConversation::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'label' => ['nullable', 'string', 'max:120'],
            'attachment_type' => ['nullable', 'string', 'max:30'],
            'channel' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $path = $file->store('inbox/'.$inboxConversation->company_id, 'public');
        $label = $validated['label'] ?? $file->getClientOriginalName();
        $mime = (string) $file->getMimeType();
        $attachmentType = $validated['attachment_type']
            ?? (str_starts_with($mime, 'image/') ? 'image' : 'document');
        $channel = InboxMessageChannel::tryFrom($validated['channel'] ?? '')
            ?? InboxMessageChannel::tryFrom($inboxConversation->last_channel ?? '')
            ?? InboxMessageChannel::WhatsApp;
        $caption = trim((string) ($validated['caption'] ?? ''));

        $messageId = null;
        if ($caption !== '') {
            $message = $this->messages->reply($inboxConversation, $caption, $request->user()->id, $channel);
            $messageId = $message->id;
        }

        CommunicationConversationAttachment::query()->create([
            'communication_conversation_id' => $inboxConversation->id,
            'communication_conversation_message_id' => $messageId,
            'attachment_type' => $attachmentType,
            'label' => $label,
            'file_path' => $path,
            'uploaded_by' => $request->user()->id,
        ]);

        $preview = $caption !== '' ? $caption : $label;
        $this->conversations->touchActivity($inboxConversation, $preview, $channel->value);

        $this->audit->record($inboxConversation, InboxAuditEventType::AttachmentAdded, $request->user()->id, [
            'summary' => $label,
        ]);

        return redirect()->route('admin.communications.inbox.index', ['conversation' => $inboxConversation->id])
            ->with('inbox_attachment_sent', true);
    }

    public function threadFeed(Request $request, CommunicationConversation $inboxConversation): JsonResponse
    {
        $this->authorize('view', $inboxConversation);

        $inboxConversation->load([
            'threadMessages.creator', 'attachments.uploader',
        ]);

        $channelFilter = $request->get('channel');
        $this->conversations->markRead($inboxConversation);
        $workspaceData = $this->workspace->forConversation($inboxConversation, $channelFilter);
        $events = $workspaceData['message_timeline'];

        return response()->json([
            'fingerprint' => $this->chatFeed->fingerprint($events),
            'html' => view('admin.communications.inbox.workspace.chat-messages', [
                'events' => $events,
                'active' => $inboxConversation,
                'inboxTurboFrame' => $request->get('turbo_frame', 'module-workspace-content'),
            ])->render(),
            'unread_count' => (int) $inboxConversation->fresh()->unread_count,
        ]);
    }

    public function downloadAttachment(
        CommunicationConversation $inboxConversation,
        CommunicationConversationAttachment $attachment,
    ): StreamedResponse {
        $this->authorize('attachments', CommunicationConversation::class);
        abort_unless($attachment->communication_conversation_id === $inboxConversation->id, 404);

        return Storage::disk('public')->download($attachment->file_path, $attachment->label);
    }

    public function destroyMessage(
        Request $request,
        CommunicationConversation $inboxConversation,
        CommunicationConversationMessage $message,
    ): RedirectResponse {
        $this->authorize('reply', CommunicationConversation::class);
        $this->authorize('view', $inboxConversation);
        abort_unless($message->communication_conversation_id === $inboxConversation->id, 404);
        abort_unless($message->direction === 'outgoing', 403);

        $this->messages->archiveMessage($message, $request->user()->id);

        return $this->redirectToConversation($inboxConversation, $request);
    }

    public function destroyAttachment(
        Request $request,
        CommunicationConversation $inboxConversation,
        CommunicationConversationAttachment $attachment,
    ): RedirectResponse {
        $this->authorize('attachments', CommunicationConversation::class);
        $this->authorize('view', $inboxConversation);
        abort_unless($attachment->communication_conversation_id === $inboxConversation->id, 404);

        $this->messages->archiveAttachment($attachment, $request->user()->id);

        return $this->redirectToConversation($inboxConversation, $request);
    }

    protected function redirectToConversation(CommunicationConversation $conversation, Request $request): RedirectResponse
    {
        return redirect()->route('admin.communications.inbox.index', array_filter([
            'conversation' => $conversation->id,
            'channel' => $request->get('channel'),
            'view' => $request->get('view'),
        ]));
    }
}
