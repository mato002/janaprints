<x-admin-layout :title="__('WhatsApp Queue')" :breadcrumbs="[['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('Queue')]]">
    @include('admin.communications.whatsapp.partials.nav')
    <x-admin.page-header :title="__('Message queue')" />
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead>
                <tr>
                    <th>{{ __('Conversation') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Queued at') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr>
                        <td class="max-w-xs truncate">{{ Str::limit($message->body, 60) }}</td>
                        <td>{{ $message->message_type->label() }}</td>
                        <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $message->status->badgeClass() }}">{{ $message->status->label() }}</span></td>
                        <td>{{ $message->queued_at?->format('d M Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('Queue is empty.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($messages->hasPages())<div class="mt-3">{{ $messages->links() }}</div>@endif
    </div>
</x-admin-layout>
