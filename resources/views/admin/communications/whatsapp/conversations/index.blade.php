<x-admin-layout :title="__('WhatsApp Conversations')" :breadcrumbs="[['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('Conversations')]]">
    @include('admin.communications.whatsapp.partials.nav')
    <x-admin.page-header :title="__('All conversations')" />
    <div class="erp-card">
        <div class="overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Unread') }}</th>
                        <th>{{ __('Last activity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conversation)
                        <tr>
                            <td><a href="{{ route('admin.communications.whatsapp.conversations.show', $conversation) }}" class="font-medium text-erp-accent" data-turbo-frame="erp-main">{{ $conversation->customer?->name ?? $conversation->conversation_code }}</a></td>
                            <td>{{ $conversation->phone_number }}</td>
                            <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $conversation->status->badgeClass() }}">{{ $conversation->status->label() }}</span></td>
                            <td class="tabular-nums">{{ $conversation->unread_count }}</td>
                            <td>{{ $conversation->last_activity_at?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No conversations.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($conversations->hasPages())<div class="mt-3">{{ $conversations->links() }}</div>@endif
    </div>
</x-admin-layout>
