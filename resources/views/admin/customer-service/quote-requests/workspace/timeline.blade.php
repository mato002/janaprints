<x-admin.record-workspace.section
    :title="__('Activity timeline')"
    class="rw-section--timeline"
    tone="work"
>
    <x-slot:actions>
        <button
            type="button"
            class="text-xs font-semibold text-slate-500 hover:text-slate-800"
            @click="timelineOpen = ! timelineOpen"
            :aria-expanded="timelineOpen"
        >
            <span x-text="timelineOpen ? @js(__('Collapse')) : @js(__('Expand'))"></span>
        </button>
    </x-slot:actions>

    <ul class="crm-360__timeline" role="list" x-show="timelineOpen" x-cloak>
        @forelse ($workspace['timeline'] as $event)
            <li class="crm-360__timeline-item">
                <span class="crm-360__timeline-dot" aria-hidden="true"></span>
                <div class="crm-360__timeline-body">
                    <div class="crm-360__timeline-head">
                        <span class="crm-360__badge crm-360__badge--activity">{{ $event['badge'] }}</span>
                        <time class="crm-360__timeline-date">{{ $event['at']?->format('d M Y, H:i') }}</time>
                    </div>
                    <span class="crm-360__timeline-title">{{ $event['title'] }}</span>
                    <p class="crm-360__timeline-meta">{{ $event['body'] }} · {{ $event['at']?->diffForHumans() }}</p>
                </div>
            </li>
        @empty
            <li class="crm-360__empty-inline">{{ __('No activity yet') }}</li>
        @endforelse
    </ul>
</x-admin.record-workspace.section>
