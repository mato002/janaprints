<x-admin.page-header :title="$header['name']" :description="$header['code']">
    <div class="flex flex-wrap items-center gap-2">
        <x-admin.enum-status-badge :status="$header['status']->value" />
        @can('update', $customer)
            <a href="{{ route('admin.crm.customers.edit', $customer) }}" class="erp-btn-secondary text-sm">{{ __('Edit customer') }}</a>
        @endcan
    </div>
</x-admin.page-header>

<div class="customer-360__header-grid mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Company') }}</span>
        <span class="customer-360__meta-value">{{ $header['company'] ?? '—' }}</span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Branch') }}</span>
        <span class="customer-360__meta-value">{{ $header['branch'] ?? '—' }}</span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Segment') }}</span>
        <span class="customer-360__meta-value">{{ $header['segments'] ? implode(', ', $header['segments']) : '—' }}</span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Contact person') }}</span>
        <span class="customer-360__meta-value">{{ $header['contact_person'] ?? '—' }}</span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Email') }}</span>
        <span class="customer-360__meta-value truncate">{{ $header['email'] ?? '—' }}</span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Phone') }}</span>
        <span class="customer-360__meta-value">{{ $header['phone'] ?? '—' }}</span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Credit limit') }}</span>
        <span class="customer-360__meta-value tabular-nums">{{ number_format((float) $header['credit_limit'], 2) }}</span>
    </div>
    <div class="customer-360__meta">
        <span class="customer-360__meta-label">{{ __('Payment terms') }}</span>
        <span class="customer-360__meta-value">{{ $header['payment_terms'] ?? '—' }}</span>
    </div>
</div>

@if (count($quickActions) > 0)
    <div class="customer-360__quick-actions mb-4 flex flex-wrap gap-2">
        @foreach ($quickActions as $action)
            <a
                href="{{ $action['url'] }}"
                @if (! empty($action['scroll'])) id="{{ $action['scroll'] }}" @endif
                class="erp-btn-secondary text-sm"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >{{ $action['label'] }}</a>
        @endforeach
    </div>
@endif
