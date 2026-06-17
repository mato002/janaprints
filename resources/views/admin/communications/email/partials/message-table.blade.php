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
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead>
                <tr>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('To') }}</th>
                    <th>{{ __('Sender') }}</th>
                    <th>{{ __('Status') }}</th>
                    @if (($viewMode ?? '') === 'inbox')
                        <th>{{ __('Failure') }}</th>
                        <th>{{ __('Retries') }}</th>
                        <th>{{ __('Last attempt') }}</th>
                    @elseif (($viewMode ?? '') === 'queued')
                        <th>{{ __('Queued') }}</th>
                    @else
                        <th>{{ __('Sent') }}</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    @php
                        $metadata = $message->provider_response['metadata'] ?? [];
                        $retryCount = (int) ($message->provider_response['retry_count'] ?? 0);
                        $lastAttempt = $message->provider_response['last_attempt_at'] ?? null;
                    @endphp
                    <tr>
                        <td>{{ Str::limit($message->subject, 40) }}</td>
                        <td class="text-xs">{{ collect($message->to_emails)->pluck('email')->join(', ') }}</td>
                        <td class="text-xs">{{ $message->account?->from_email ?? '—' }}</td>
                        <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $message->status->badgeClass() }}">{{ $message->status->label() }}</span></td>
                        @if (($viewMode ?? '') === 'inbox')
                            <td class="max-w-[12rem] truncate text-xs text-red-600" title="{{ $message->failure_reason }}">{{ Str::limit($message->failure_reason, 40) ?: '—' }}</td>
                            <td class="text-xs">{{ $retryCount }}</td>
                            <td class="text-xs">{{ $lastAttempt ? \Illuminate\Support\Carbon::parse($lastAttempt)->format('d M Y H:i') : ($message->failed_at?->format('d M Y H:i') ?? '—') }}</td>
                        @elseif (($viewMode ?? '') === 'queued')
                            <td class="text-xs">{{ $message->queued_at?->format('d M Y H:i') ?? '—' }}</td>
                        @else
                            <td class="text-xs">{{ $message->sent_at?->format('d M Y H:i') ?? '—' }}</td>
                        @endif
                        <td class="whitespace-nowrap text-right">
                            <button type="button" class="text-erp-accent text-sm" @click="openDrawer({{ $message->id }})">{{ __('View') }}</button>
                            @if (($viewMode ?? '') === 'queued')
                                @can('cancel', $message)
                                    <form method="POST" action="{{ route('admin.communications.email.messages.cancel', $message) }}" class="inline ml-2">@csrf
                                        <button type="submit" class="text-sm text-red-600" onclick="return confirm(@js(__('Cancel this queued email?')))">{{ __('Cancel') }}</button>
                                    </form>
                                @endcan
                            @elseif (($viewMode ?? '') === 'inbox')
                                @can('retry', $message)
                                    <form method="POST" action="{{ route('admin.communications.email.messages.retry', $message) }}" class="inline ml-2">@csrf
                                        <button type="submit" class="text-sm text-erp-accent">{{ __('Retry') }}</button>
                                    </form>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-6 text-center text-slate-500">{{ __('No messages.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($messages->hasPages())<div class="mt-3">{{ $messages->links() }}</div>@endif
    </div>

    @include('admin.communications.email.partials.detail-drawer')
</div>
