@props(['customer', 'inboxConversations', 'communicationTimeline'])

@can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
    <div class="crm-360__channel-card">
        <div class="crm-360__card-head">
            <h3 class="erp-card-title">{{ __('Shared inbox') }}</h3>
            <form method="POST" action="{{ route('admin.communications.inbox.customers.start', $customer) }}" data-turbo-frame="erp-main">
                @csrf
                <x-admin.crm-btn type="submit" variant="primary" size="sm">
                    <x-slot:icon>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </x-slot:icon>
                    {{ __('Open / continue thread') }}
                </x-admin.crm-btn>
            </form>
        </div>
        @if ($inboxConversations->isNotEmpty())
            <ul class="mt-2 space-y-1 text-sm">
                @foreach ($inboxConversations as $conv)
                    <li>
                        <a href="{{ route('admin.communications.inbox.index', ['conversation' => $conv->id]) }}" class="crm-360__row-link" data-turbo-frame="erp-main">
                            {{ $conv->conversation_code }} · {{ $conv->status->label() }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
        @can('viewAny', App\Models\Communications\CommunicationLog::class)
            <div class="mt-3 border-t pt-3">
                <p class="text-xs font-semibold uppercase text-slate-500 mb-2">{{ __('Full communication timeline') }}</p>
                <x-admin.communication-timeline :logs="$communicationTimeline" :compact="true" />
            </div>
        @endcan
    </div>
@endcan
