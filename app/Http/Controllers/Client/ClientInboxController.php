<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Communications\Inbox\CommunicationConversationAttachment;
use App\Services\Client\ClientPortalCommunicationService;
use App\Services\Client\ClientPortalInboxService;
use App\Support\Communications\Inbox\InboxMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientInboxController extends Controller
{
    use ResolvesClientCustomer;

    public function index(
        Request $request,
        ClientPortalInboxService $inbox,
        ClientPortalCommunicationService $communications,
    ): View {
        $customer = $this->clientCustomer();
        $portalUser = $this->clientUser();
        $conversation = $inbox->resolveConversation($customer, $portalUser);
        $inbox->markReadForClient($conversation);
        $feed = $inbox->feed($conversation->fresh());
        $logs = $communications->paginateForCustomer($customer);

        return view('client.communications.index', [
            'customer' => $customer,
            'conversation' => $conversation,
            'feed' => $feed,
            'feed_fingerprint' => $inbox->feedFingerprint($feed),
            'logs' => $logs,
            'communications' => $communications,
            'unread_count' => 0,
            'show_history' => $request->boolean('history'),
        ]);
    }

    public function feed(Request $request, ClientPortalInboxService $inbox): JsonResponse
    {
        $customer = $this->clientCustomer();
        $portalUser = $this->clientUser();
        $conversation = $inbox->resolveConversation($customer, $portalUser);
        $inbox->assertClientOwnsConversation($conversation, $customer);

        if ($request->boolean('mark_read', true)) {
            $inbox->markReadForClient($conversation);
        }

        $feed = $inbox->feed($conversation->fresh());

        return response()->json([
            'fingerprint' => $inbox->feedFingerprint($feed),
            'html' => $inbox->renderFeedHtml($feed),
            'unread' => $inbox->unreadCountForCustomer($customer),
        ]);
    }

    public function storeMessage(Request $request, ClientPortalInboxService $inbox, InboxMessageService $messages): RedirectResponse|JsonResponse
    {
        $customer = $this->clientCustomer();
        $portalUser = $this->clientUser();
        $conversation = $inbox->resolveConversation($customer, $portalUser);
        $inbox->assertClientOwnsConversation($conversation, $customer);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $messages->receiveFromCustomer($conversation, trim($validated['body']), (int) $portalUser->id);

        if ($request->wantsJson()) {
            $feed = $inbox->feed($conversation->fresh());

            return response()->json([
                'ok' => true,
                'fingerprint' => $inbox->feedFingerprint($feed),
                'html' => $inbox->renderFeedHtml($feed),
            ]);
        }

        return redirect()
            ->route('client.communications.index')
            ->with('status', __('Message sent.'));
    }

    public function storeAttachment(
        Request $request,
        ClientPortalInboxService $inbox,
        InboxMessageService $messages,
    ): RedirectResponse|JsonResponse {
        $customer = $this->clientCustomer();
        $portalUser = $this->clientUser();
        $conversation = $inbox->resolveConversation($customer, $portalUser);
        $inbox->assertClientOwnsConversation($conversation, $customer);

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ]);

        $file = $request->file('file');
        $path = $file->store('inbox/'.$conversation->company_id, 'public');
        $label = $file->getClientOriginalName();
        $mime = (string) $file->getMimeType();
        $attachmentType = str_starts_with($mime, 'image/') ? 'image' : 'document';

        $attachment = CommunicationConversationAttachment::query()->create([
            'communication_conversation_id' => $conversation->id,
            'attachment_type' => $attachmentType,
            'label' => $label,
            'file_path' => $path,
        ]);

        $messages->storeClientAttachment(
            $conversation,
            $attachment,
            $validated['caption'] ?? null,
            (int) $portalUser->id,
        );

        if ($request->wantsJson()) {
            $feed = $inbox->feed($conversation->fresh());

            return response()->json([
                'ok' => true,
                'fingerprint' => $inbox->feedFingerprint($feed),
                'html' => $inbox->renderFeedHtml($feed),
            ]);
        }

        return redirect()
            ->route('client.communications.index')
            ->with('status', __('File sent.'));
    }

    public function downloadAttachment(
        int $conversation,
        CommunicationConversationAttachment $attachment,
        ClientPortalInboxService $inbox,
    ): StreamedResponse {
        $customer = $this->clientCustomer();
        $portalUser = $this->clientUser();
        $thread = $inbox->resolveConversation($customer, $portalUser);
        abort_unless($thread->id === $conversation, 404);
        abort_unless($attachment->communication_conversation_id === $thread->id, 404);
        abort_unless($attachment->file_path, 404);

        return Storage::disk('public')->download($attachment->file_path, $attachment->label ?? 'attachment');
    }

    public function unread(ClientPortalInboxService $inbox): JsonResponse
    {
        $customer = $this->clientCustomer();

        return response()->json([
            'unread' => $inbox->unreadCountForCustomer($customer),
        ]);
    }
}
