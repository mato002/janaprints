@props(['customer', 'conversations', 'whatsappTimeline'])

@can('viewAny', App\Models\Communications\WhatsappConversation::class)
    <div class="crm-360__channel-card">
        <div class="crm-360__card-head">
            <h3 class="erp-card-title">{{ __('WhatsApp') }}</h3>
            <x-admin.crm-btn
                variant="outline"
                size="sm"
                :href="route('admin.communications.whatsapp.inbox', ['q' => $customer->phone])"
                data-turbo-frame="erp-main"
            >
                <x-slot:icon>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </x-slot:icon>
                {{ __('Open inbox') }}
            </x-admin.crm-btn>
        </div>

        @if ($conversations->isNotEmpty())
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($conversations as $conversation)
                    <li>
                        <a href="{{ route('admin.communications.whatsapp.conversations.show', $conversation) }}" class="crm-360__row-link" data-turbo-frame="erp-main">
                            {{ $conversation->conversation_code }}
                        </a>
                        <span class="text-slate-500"> · {{ $conversation->status->label() }}</span>
                        @if ($conversation->unread_count)<span class="ml-1 rounded-full bg-erp-accent px-1.5 text-[10px] text-white">{{ $conversation->unread_count }}</span>@endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-2 text-sm text-slate-500">{{ __('No WhatsApp conversations linked yet.') }}</p>
        @endif

        @can('viewAny', App\Models\Communications\CommunicationLog::class)
            <div class="mt-4 border-t pt-3">
                <p class="text-xs font-semibold uppercase text-slate-500 mb-2">{{ __('WhatsApp timeline (COM-4)') }}</p>
                <x-admin.communication-timeline :logs="$whatsappTimeline" :compact="true" />
            </div>
        @endcan
    </div>
@endcan
