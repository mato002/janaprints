<x-admin-layout :title="__('Delivery audit')" :breadcrumbs="[['label' => __('Delivery'), 'url' => route('admin.communications.whatsapp.delivery.index')], ['label' => '#'.$message->id]]">
    @include('admin.communications.whatsapp.partials.nav')
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Message') }}</h2>
            <pre class="mt-2 whitespace-pre-wrap text-sm">{{ $message->body }}</pre>
            <p class="mt-2 text-xs text-slate-500">{{ $message->status->label() }} · {{ $message->message_type->label() }}</p>
        </div>
        <div class="erp-card">
            <h2 class="erp-card-title">{{ __('Provider response') }}</h2>
            <pre class="mt-2 text-xs overflow-auto max-h-48">{{ json_encode($message->provider_response, JSON_PRETTY_PRINT) }}</pre>
        </div>
        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title">{{ __('Delivery events') }}</h2>
            <ul class="mt-2 space-y-2 text-sm">
                @foreach ($message->deliveryEvents as $event)
                    <li class="border-b pb-2">
                        <span class="font-medium">{{ $event->event }}</span>
                        @if ($event->status_snapshot)<span class="text-slate-500"> → {{ $event->status_snapshot }}</span>@endif
                        <span class="block text-xs text-slate-400">{{ $event->created_at?->format('d M Y H:i:s') }}</span>
                    </li>
                @endforeach
            </ul>
            @if ($message->communicationLog)
                <div class="mt-3">
                    <x-admin.crm-btn variant="outline" size="sm" :href="route('admin.communications.logs.show', $message->communicationLog)" data-turbo-frame="erp-main">{{ __('View COM-4 communication log') }}</x-admin.crm-btn>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
