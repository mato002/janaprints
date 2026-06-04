<x-admin-layout :title="__('SMS Queue')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('SMS Queue')]]">
    @include('admin.communications.sms.partials.nav')
    <x-admin.page-header :title="__('SMS message queue')" />

    <form method="GET" class="erp-card mb-4 flex flex-wrap gap-2" data-turbo-frame="erp-main">
        <select name="queue_status" class="erp-input erp-input--sm" onchange="this.form.submit()">
            <option value="">{{ __('All queue statuses') }}</option>
            @foreach (\App\Enums\SmsMessageQueueStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(request('queue_status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="delivery_status" class="erp-input erp-input--sm" onchange="this.form.submit()">
            <option value="">{{ __('All delivery statuses') }}</option>
            @foreach (\App\Enums\SmsDeliveryStatus::cases() as $s)
                <option value="{{ $s->value }}" @selected(request('delivery_status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
    </form>

    <div class="erp-card overflow-hidden p-0">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Campaign') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Queue') }}</th>
                    <th>{{ __('Delivery') }}</th>
                    <th>{{ __('Segments') }}</th>
                    <th>{{ __('Attempts') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($messages as $message)
                    <tr>
                        <td>{{ $message->campaign?->name }}</td>
                        <td class="font-mono text-xs">{{ $message->phone_number }}</td>
                        <td>{{ $message->queue_status->label() }}</td>
                        <td>{{ $message->delivery_status?->label() ?? '—' }}</td>
                        <td class="tabular-nums">{{ $message->segments_count }}</td>
                        <td class="tabular-nums">{{ $message->attempts }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No messages in queue.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($messages->hasPages())
            <div class="border-t px-4 py-3">{{ $messages->links() }}</div>
        @endif
    </div>
</x-admin-layout>
