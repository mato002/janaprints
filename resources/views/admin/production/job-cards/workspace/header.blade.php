<x-admin.page-header :title="$header['job_number']" :description="$header['customer_name']">
    <div class="flex flex-wrap items-center gap-2">
        <x-admin.enum-status-badge :status="$header['status']->value" />
        <span class="erp-badge erp-badge--draft">{{ str_replace('_', ' ', $header['priority']->value) }}</span>
        @if ($header['is_delayed'])
            <span class="text-sm font-medium text-red-600">{{ __('Delayed') }}</span>
        @endif
        @can('update', $jobCard)
            <a href="{{ route('admin.production.job-cards.edit', $jobCard) }}" class="erp-btn-secondary text-sm">{{ __('Edit job') }}</a>
        @endcan
    </div>
</x-admin.page-header>

<div class="job-360__header-grid mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Customer') }}</span>
        <span class="job-360__meta-value">{{ $header['customer_name'] ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Sales order') }}</span>
        <span class="job-360__meta-value">{{ $header['sales_order_number'] ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Quotation') }}</span>
        <span class="job-360__meta-value">{{ $header['quotation_number'] ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Artwork request') }}</span>
        <span class="job-360__meta-value">{{ $header['artwork_number'] ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Production type') }}</span>
        <span class="job-360__meta-value">{{ str_replace('_', ' ', $header['production_type']->value) }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Due date') }}</span>
        <span class="job-360__meta-value">{{ $header['due_date']?->format('Y-m-d') ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Work center') }}</span>
        <span class="job-360__meta-value">{{ $header['work_center'] ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Progress') }}</span>
        <span class="job-360__meta-value tabular-nums">{{ $header['progress_percent'] }}%</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Branch') }}</span>
        <span class="job-360__meta-value">{{ $header['branch'] ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Created by') }}</span>
        <span class="job-360__meta-value">{{ $header['created_by'] ?? '—' }}</span>
    </div>
    <div class="job-360__meta">
        <span class="job-360__meta-label">{{ __('Created') }}</span>
        <span class="job-360__meta-value">{{ $header['created_at']?->format('Y-m-d H:i') ?? '—' }}</span>
    </div>
</div>

@if (count($quickActions) > 0)
    <div class="job-360__quick-actions workspace-action-bar mb-4 flex flex-wrap gap-2">
        @foreach ($quickActions as $action)
            @if (($action['type'] ?? null) === 'post')
                <form method="POST" action="{{ $action['url'] }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-primary text-sm">{{ $action['label'] }}</button>
                </form>
            @else
                <a
                    href="{{ $action['url'] }}"
                    class="erp-btn-secondary text-sm"
                    data-turbo-frame="erp-main"
                    data-turbo-action="advance"
                >{{ $action['label'] }}</a>
            @endif
        @endforeach
    </div>
@endif
