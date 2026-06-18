@php
    $health = $dashboard['integrations'] ?? [];
    $statusColors = ['green' => 'bg-emerald-500', 'yellow' => 'bg-amber-400', 'red' => 'bg-red-500'];
@endphp

<section class="exec-integration-health rounded-lg border border-erp-border bg-white p-3 md:p-4" aria-label="{{ __('Integration health') }}">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-erp-primary">{{ __('Integration Health') }}</h2>
        <a href="{{ route('admin.workspaces.administration.section', 'integrations') }}" class="text-xs text-erp-accent hover:underline">{{ __('Manage') }}</a>
    </div>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Email'), 'status' => $health['email']['status'] ?? 'yellow', 'value' => $health['email']['label'] ?? '—', 'route' => $health['email']['route'] ?? null],
            ['label' => __('SMS'), 'status' => $health['sms']['status'] ?? 'yellow', 'value' => $health['sms']['label'] ?? '—', 'route' => $health['sms']['route'] ?? null],
            ['label' => __('Webhooks'), 'status' => $health['webhooks']['status'] ?? 'yellow', 'value' => ($health['webhooks']['active'] ?? 0).' '.__('active'), 'route' => $health['webhooks']['route'] ?? null],
            ['label' => __('Providers'), 'status' => $health['providers']['status'] ?? 'yellow', 'value' => ($health['providers']['connected'] ?? 0).' '.__('connected'), 'route' => $health['providers']['route'] ?? null],
            ['label' => __('API Keys'), 'status' => $health['api_keys']['status'] ?? 'yellow', 'value' => (string) ($health['api_keys']['count'] ?? 0), 'route' => $health['api_keys']['route'] ?? null],
        ] as $item)
            @php $href = ! empty($item['route']) && Route::has($item['route']) ? route($item['route']) : null; @endphp
            <a @if($href) href="{{ $href }}" @endif class="flex items-center gap-2 rounded-md border border-erp-border px-3 py-2 text-xs {{ $href ? 'hover:bg-erp-page' : '' }}">
                <span class="h-2 w-2 shrink-0 rounded-full {{ $statusColors[$item['status']] ?? $statusColors['yellow'] }}" aria-hidden="true"></span>
                <span>
                    <span class="block font-medium text-slate-700">{{ $item['label'] }}</span>
                    <span class="text-slate-500">{{ $item['value'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
</section>
