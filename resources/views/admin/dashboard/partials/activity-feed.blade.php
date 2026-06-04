<section class="exec-panel exec-panel--activity h-full">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Activity Feed') }}</h2>
        @can('viewAny', App\Models\ActivityLog::class)
            <a href="{{ route('admin.activity-logs.index') }}" data-turbo-frame="erp-main" class="text-[11px] font-medium text-erp-accent hover:underline">{{ __('View all') }}</a>
        @endcan
    </div>
    <p class="mb-2 text-[10px] text-slate-500">{{ __('Newest system events first') }}</p>
    <div class="exec-activity-feed exec-activity-feed--prominent">
        @if (count($dashboard['activity']) === 0)
            <x-admin.exec-empty-state
                :title="__('No activity yet')"
                :description="__('Quotes, jobs, invoices, and payments will stream here.')"
                compact
            />
        @else
            <x-admin.activity-timeline :items="$dashboard['activity']" />
        @endif
    </div>
</section>
