@php
    use Illuminate\Support\Str;
@endphp

<div
    x-data="{
        drawerOpen: false,
        loading: false,
        detail: null,
        async openDrawer(messageId) {
            this.drawerOpen = true;
            this.loading = true;
            this.detail = null;
            try {
                const response = await fetch(`{{ url('admin/communications/email/messages') }}/${messageId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (response.ok) {
                    const data = await response.json();
                    this.detail = data.message;
                }
            } finally {
                this.loading = false;
            }
        },
        closeDrawer() {
            this.drawerOpen = false;
            this.detail = null;
        },
    }"
>
    <div class="mb-3 flex items-center justify-between gap-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">
            {{ $listTitle ?? __('Mailbox') }}
            @if (isset($messages) && method_exists($messages, 'total'))
                <span class="ml-1 font-normal normal-case text-slate-400">({{ $messages->total() }})</span>
            @endif
        </h2>
    </div>

    <div class="erp-card divide-y overflow-hidden">
        @forelse ($messages as $message)
            @php
                $recipient = collect($message->to_emails)->first();
                $recipientName = $recipient['name'] ?? null;
                $recipientEmail = $recipient['email'] ?? null;
                $displayName = filled($recipientName) ? $recipientName : ($recipientEmail ?: __('Unknown recipient'));
                $preview = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $message->body)) ?? ''), 110);
                $when = $message->sent_at ?? $message->failed_at ?? $message->queued_at ?? $message->created_at;
                $needsAttention = in_array($message->status->value, ['failed', 'bounced'], true);
            @endphp
            <div class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50">
                <button
                    type="button"
                    class="min-w-0 flex-1 text-left"
                    @click="openDrawer({{ $message->id }})"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                @if ($needsAttention)
                                    <span class="h-2 w-2 shrink-0 rounded-full bg-red-500" title="{{ __('Needs attention') }}"></span>
                                @endif
                                <p class="truncate font-semibold text-erp-primary">{{ $displayName }}</p>
                                @if ($message->attachments->isNotEmpty())
                                    <span class="shrink-0 text-slate-400" title="{{ __('Has attachment') }}">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 truncate text-sm font-medium text-slate-700">{{ $message->subject ?: __('(No subject)') }}</p>
                            <p class="mt-0.5 truncate text-sm text-slate-500">{{ $preview ?: __('No preview available') }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs text-slate-400">{{ $when?->diffForHumans() }}</p>
                            <span class="mt-1 inline-flex rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $message->status->badgeClass() }}">{{ $message->status->label() }}</span>
                        </div>
                    </div>
                </button>
                <div class="flex shrink-0 flex-col items-end gap-1 pt-0.5">
                    <button type="button" class="text-sm text-erp-accent" @click="openDrawer({{ $message->id }})">{{ __('Open') }}</button>
                    @if (($viewMode ?? '') === 'queued')
                        @can('cancel', $message)
                            <form method="POST" action="{{ route('admin.communications.email.messages.cancel', $message) }}">@csrf
                                <button type="submit" class="text-sm text-red-600" onclick="return confirm(@js(__('Cancel this queued email?')))">{{ __('Cancel') }}</button>
                            </form>
                        @endcan
                    @elseif (($viewMode ?? '') === 'inbox')
                        @can('retry', $message)
                            <form method="POST" action="{{ route('admin.communications.email.messages.retry', $message) }}">@csrf
                                <button type="submit" class="text-sm text-erp-accent">{{ __('Retry') }}</button>
                            </form>
                        @endcan
                    @elseif (($viewMode ?? '') === 'drafts')
                        @can('send', $message)
                            <form method="POST" action="{{ route('admin.communications.email.messages.send', $message) }}">@csrf
                                <button type="submit" class="text-sm text-erp-accent">{{ __('Send') }}</button>
                            </form>
                        @endcan
                    @else
                        @can('create', App\Models\Communications\EmailCampaign::class)
                            <a
                                href="{{ route('admin.communications.email.compose', ['to' => $recipientEmail]) }}"
                                data-turbo-frame="erp-main"
                                class="text-sm text-erp-accent"
                            >{{ __('Reply') }}</a>
                        @endcan
                    @endif
                </div>
            </div>
        @empty
            <div class="space-y-3 px-4 py-10 text-center">
                <p class="text-sm text-slate-500">{{ $emptyMessage ?? __('No emails in this folder yet.') }}</p>
                @can('create', App\Models\Communications\EmailCampaign::class)
                    <a href="{{ route('admin.communications.email.compose') }}" data-turbo-frame="erp-main" class="erp-btn erp-btn--primary inline-flex">
                        {{ __('Compose Email') }}
                    </a>
                @endcan
            </div>
        @endforelse
    </div>

    @if ($messages->hasPages())
        <div class="mt-3">{{ $messages->links() }}</div>
    @endif

    @include('admin.communications.email.partials.detail-drawer')
</div>
