@php
    $primary = $primaryAction ?? null;
    $secondary = $secondaryActions ?? [];
    $links = $linkActions ?? [];
    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    $activeTab = $activeTab ?? null;
    $eligible = (bool) ($completion['eligible'] ?? false);
    $blockers = $completion['blockers'] ?? [];
    $remaining = count($blockers);
    $suggestedQty = $completion['suggested_quantity_completed'] ?? null;
    $postLabel = $suggestedQty
        ? __('Post :qty finished goods', ['qty' => number_format((float) $suggestedQty, 0)])
        : __('Post to finished goods');
    $onOutputsTab = $activeTab === 'outputs';
@endphp

<div class="job-360-workflow mb-4 rounded-lg border border-erp-border bg-white px-4 py-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            @if ($primary)
                @if (($primary['type'] ?? '') === 'post')
                    <form method="POST" action="{{ $primary['url'] }}" class="inline">
                        @csrf
                        <button type="submit" class="{{ ($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-sm">
                            {{ $primary['label'] }}
                        </button>
                    </form>
                @else
                    <a
                        href="{{ $primary['url'] }}"
                        class="{{ ($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-sm"
                        data-turbo-frame="erp-main"
                        data-turbo-action="advance"
                    >{{ $primary['label'] }}</a>
                @endif
            @endif

            @can('production.outputs.post')
                @if (! $onOutputsTab && (($completion['eligible'] ?? false) || ($finishedItems ?? collect())->isNotEmpty()))
                    @if ($eligible)
                        <button type="button" class="erp-btn-secondary text-sm" data-open-dialog="complete-fg-modal">{{ $postLabel }}</button>
                    @else
                        <button type="button" class="erp-btn-secondary text-sm opacity-60" disabled>
                            {{ __('Post to finished goods') }}
                        </button>
                        @if ($remaining > 0)
                            <span class="text-xs text-amber-700">{{ trans_choice(':count requirement remaining|:count requirements remaining', $remaining, ['count' => $remaining]) }}</span>
                        @endif
                    @endif
                    <a
                        href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'outputs']) }}"
                        class="erp-link text-sm self-center"
                        data-turbo-frame="erp-main"
                    >{{ __('Finished Goods') }}</a>
                @endif
            @endcan

            @foreach ($secondary as $action)
                @if (($action['type'] ?? '') === 'post')
                    <form method="POST" action="{{ $action['url'] }}" class="inline">
                        @csrf
                        <button type="submit" class="erp-btn-secondary text-sm">{{ $action['label'] }}</button>
                    </form>
                @else
                    <a href="{{ $action['url'] }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ $action['label'] }}</a>
                @endif
            @endforeach

            @can('transition', $jobCard)
                @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::Cancelled))
                    <form method="POST" action="{{ route('admin.production.job-cards.cancel', $jobCard) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Cancel job') }}</button>
                    </form>
                @endif
            @endcan

            @can('delete', $jobCard)
                <form method="POST" action="{{ route('admin.production.job-cards.destroy', $jobCard) }}" class="inline" onsubmit="return confirm(@js(__('Permanently delete this job card? This cannot be undone.')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-700 hover:underline">{{ __('Delete job') }}</button>
                </form>
            @endcan
        </div>

        @if (count($links) > 0)
            <details class="relative text-sm">
                <summary class="cursor-pointer list-none text-slate-600 hover:text-erp-primary [&::-webkit-details-marker]:hidden">
                    {{ __('Related links') }} ▾
                </summary>
                <div class="absolute right-0 z-10 mt-1 min-w-[11rem] rounded-md border border-erp-border bg-white py-1 shadow-lg">
                    @foreach ($links as $link)
                        <a
                            href="{{ $link['url'] }}"
                            class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                            @if (($link['target'] ?? null) === '_blank') target="_blank" rel="noopener" @else data-turbo-frame="erp-main" @endif
                        >{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </details>
        @endif
    </div>

    @can('schedule', $jobCard)
        <form method="POST" action="{{ route('admin.production.job-cards.schedule', $jobCard) }}" class="mt-3 flex flex-wrap items-end gap-2 border-t border-erp-border pt-3">
            @csrf
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500">{{ __('Planned start') }}</label>
                <input type="date" name="planned_start_date" class="erp-input text-sm py-1" value="{{ $jobCard->planned_start_date?->format('Y-m-d') }}" required>
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500">{{ __('Planned end') }}</label>
                <input type="date" name="planned_end_date" class="erp-input text-sm py-1" value="{{ $jobCard->planned_end_date?->format('Y-m-d') }}" required>
            </div>
            <button type="submit" class="erp-btn-secondary text-sm py-1">{{ __('Update schedule') }}</button>
        </form>
    @endcan
</div>

@can('production.outputs.post')
    @unless ($onOutputsTab)
        @include('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'finishedItems' => $finishedItems ?? collect(),
        ])
    @endunless
@endcan
