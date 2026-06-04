<div class="crm-360__tab-stack">
    <div class="crm-360__tab-toolbar">
        @can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
            <form method="POST" action="{{ route('admin.communications.inbox.customers.start', $customer) }}" data-turbo-frame="erp-main">
                @csrf
                <x-admin.crm-btn type="submit" variant="primary" size="sm">
                    <x-slot:icon>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </x-slot:icon>
                    {{ __('Open shared inbox') }}
                </x-admin.crm-btn>
            </form>
        @endcan
        @can('viewAny', App\Models\Communications\WhatsappConversation::class)
            <x-admin.crm-btn
                variant="outline"
                size="sm"
                :href="route('admin.communications.whatsapp.inbox', ['q' => $customer->phone])"
                data-turbo-frame="erp-main"
            >
                <x-slot:icon>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </x-slot:icon>
                {{ __('WhatsApp inbox') }}
            </x-admin.crm-btn>
        @endcan
    </div>

    @include('admin.communications.whatsapp.partials.customer-panel', [
        'customer' => $customer,
        'conversations' => $whatsappConversations,
        'whatsappTimeline' => $whatsappTimeline,
    ])

    @include('admin.communications.email.partials.customer-panel', [
        'customer' => $customer,
        'emailTimeline' => $emailTimeline,
    ])

    @include('admin.communications.inbox.partials.customer-panel', [
        'customer' => $customer,
        'inboxConversations' => $inboxConversations,
        'communicationTimeline' => $communicationTimeline,
    ])
</div>
