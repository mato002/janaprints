<x-admin-layout :title="__('WhatsApp Delivery')" :breadcrumbs="[['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('Delivery')]]">
    @include('admin.communications.whatsapp.partials.nav')
    <x-admin.page-header :title="__('Delivery status')" />
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead>
                <tr>
                    <th>{{ __('Message') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Provider ref') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr>
                        <td class="max-w-sm truncate">{{ Str::limit($message->body, 48) }}</td>
                        <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $message->status->badgeClass() }}">{{ $message->status->label() }}</span></td>
                        <td class="text-xs font-mono">{{ $message->provider_message_ref ?? '—' }}</td>
                        <td><a href="{{ route('admin.communications.whatsapp.delivery.show', $message) }}" class="text-erp-accent text-sm" data-turbo-frame="erp-main">{{ __('Audit') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('No messages.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($messages->hasPages())<div class="mt-3">{{ $messages->links() }}</div>@endif
    </div>
</x-admin-layout>
