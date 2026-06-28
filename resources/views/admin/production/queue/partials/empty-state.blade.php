<x-admin.empty-state
    icon="switch-horizontal"
    :title="__('No queued jobs found')"
    :description="__('Adjust filters or schedule jobs into production.')"
>
    @if (auth()->user()?->can('production.view'))
        <x-slot:action>
            <div class="flex flex-wrap items-center justify-center gap-2">
                <a href="{{ route('admin.production.job-cards.index') }}" class="erp-btn-primary text-sm" data-turbo-frame="erp-main">{{ __('View Job Cards') }}</a>
                @if (auth()->user()?->can('production.scheduling.view'))
                    <a href="{{ route('admin.production.scheduling.index') }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Open Scheduling') }}</a>
                @endif
            </div>
        </x-slot:action>
    @endif
</x-admin.empty-state>
