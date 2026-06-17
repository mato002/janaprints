<x-admin-layout :title="__('Delivery audit')">
    @include('admin.communications.email.partials.nav')
    <div class="grid gap-4 lg:grid-cols-2">
        <div class="erp-card">
            <h2 class="erp-card-title">{{ $message->subject }}</h2>
            <p class="text-sm text-slate-500">{{ $message->status->label() }}</p>
            <pre class="mt-2 text-sm whitespace-pre-wrap">{{ app(\App\Support\Hr\PayrollConfidentialityService::class)->emailBodyForViewer($message) }}</pre>
        </div>
        <div class="erp-card text-sm space-y-2">
            <div class="flex justify-between"><span>{{ __('Sent') }}</span><span>{{ $message->sent_at?->format('d M Y H:i') ?? '—' }}</span></div>
            <div class="flex justify-between"><span>{{ __('Delivered') }}</span><span>{{ $message->delivered_at?->format('d M Y H:i') ?? '—' }}</span></div>
            <div class="flex justify-between"><span>{{ __('Opened') }}</span><span>{{ $message->opened_at?->format('d M Y H:i') ?? '—' }}</span></div>
            <div class="flex justify-between"><span>{{ __('Clicked') }}</span><span>{{ $message->clicked_at?->format('d M Y H:i') ?? '—' }}</span></div>
            <div class="flex justify-between"><span>{{ __('Bounced') }}</span><span>{{ $message->bounced_at?->format('d M Y H:i') ?? '—' }}</span></div>
            @if ($message->failure_reason)<p class="text-red-600">{{ $message->failure_reason }}</p>@endif
        </div>
        <div class="erp-card lg:col-span-2">
            <h2 class="erp-card-title">{{ __('Delivery events') }}</h2>
            <ul class="mt-2 space-y-2 text-sm">
                @foreach ($message->deliveryEvents as $event)
                    <li class="border-b pb-2"><strong>{{ $event->event }}</strong> · {{ $event->created_at }}</li>
                @endforeach
            </ul>
            @if ($message->communicationLog)
                <a href="{{ route('admin.communications.logs.show', $message->communicationLog) }}" class="mt-3 inline-block text-erp-accent text-sm" data-turbo-frame="erp-main">{{ __('COM-4 communication log') }}</a>
            @endif
        </div>
    </div>
</x-admin-layout>
