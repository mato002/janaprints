@if (! empty($orderPresentation['production']))
    @php $production = $orderPresentation['production']; @endphp
    <x-admin.card class="border-emerald-200 bg-emerald-50/50">
        <h3 class="mb-2 text-sm font-semibold text-emerald-900">{{ __('Production handoff') }}</h3>
        <dl class="grid grid-cols-2 gap-x-3 gap-y-2 text-sm">
            <div>
                <dt class="text-xs text-emerald-700">{{ __('Job card') }}</dt>
                <dd class="font-mono font-medium">{{ $production['job_card_number'] }}</dd>
            </div>
            <div>
                <dt class="text-xs text-emerald-700">{{ __('Department') }}</dt>
                <dd>{{ $production['department_label'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-emerald-700">{{ __('Queue status') }}</dt>
                <dd>{{ $production['queue_status'] }}</dd>
            </div>
            <div>
                <dt class="text-xs text-emerald-700">{{ __('Job status') }}</dt>
                <dd>{{ $production['job_status'] }}</dd>
            </div>
        </dl>
        @if ($production['department_queue_url'] ?? null)
            <a href="{{ $production['department_queue_url'] }}" class="mt-3 inline-flex text-xs text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('Open department register') }}</a>
        @endif
    </x-admin.card>
@endif
