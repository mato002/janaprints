<div class="job-360__header mb-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-semibold text-slate-900">{{ $header['job_number'] }}</h1>
                <x-admin.enum-status-badge :status="$header['status']->value" />
                <span class="erp-badge erp-badge--draft text-xs">{{ str_replace('_', ' ', $header['priority']->value) }}</span>
                @if ($header['is_delayed'])
                    <span class="text-xs font-medium text-red-600">{{ __('Delayed') }}</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-600">
                {{ $header['customer_name'] ?? __('No customer') }}
                @if ($header['sales_order_number'])
                    <span class="text-slate-400">·</span>
                    <span class="font-mono text-xs">{{ $header['sales_order_number'] }}</span>
                @endif
                @if ($header['product_name'])
                    <span class="text-slate-400">·</span>
                    {{ $header['product_name'] }}
                @endif
            </p>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                <span>{{ __('Due') }}: <strong class="font-medium text-slate-700">{{ $header['due_date']?->format('Y-m-d') ?? '—' }}</strong></span>
                <span>{{ __('Type') }}: <strong class="font-medium text-slate-700">{{ str_replace('_', ' ', $header['production_type']->value) }}</strong></span>
                <span>{{ __('Work center') }}: <strong class="font-medium text-slate-700">{{ $header['work_center'] ?? '—' }}</strong></span>
                <span>{{ __('Progress') }}: <strong class="font-medium text-slate-700 tabular-nums">{{ $header['progress_percent'] }}%</strong></span>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <a href="{{ route('admin.production.floor') }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Back to floor') }}</a>
            @can('update', $jobCard)
                <a href="{{ route('admin.production.job-cards.edit', $jobCard) }}" class="erp-btn-secondary text-sm">{{ __('Edit') }}</a>
            @endcan
        </div>
    </div>
</div>
