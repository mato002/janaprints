<section class="exec-panel">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Recent Activity') }}</h2>
        @can('viewAny', App\Models\ActivityLog::class)
            <a href="{{ route('admin.activity-logs.index') }}" data-turbo-frame="erp-main" class="text-[11px] font-medium text-erp-accent hover:underline">{{ __('View all') }}</a>
        @endcan
    </div>
    <div class="exec-activity-feed">
        <x-admin.activity-timeline :items="$dashboard['activity']" />
    </div>
</section>
