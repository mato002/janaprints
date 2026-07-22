<x-admin.card class="mb-4">
    <div class="flex flex-wrap gap-2">
        @foreach (['month' => __('Month'), 'week' => __('Week'), 'upcoming' => __('Upcoming'), 'overdue' => __('Overdue')] as $view => $label)
            <a
                href="{{ $hubUrl }}?{{ http_build_query(['tab' => 'calendar', 'view' => $view]) }}"
                data-turbo-frame="module-workspace-content"
                class="{{ $activeView === $view ? 'erp-btn-primary' : 'erp-btn-secondary' }}"
            >{{ $label }}</a>
        @endforeach
    </div>
</x-admin.card>

<x-admin.card>
    <p class="mb-3 text-sm text-slate-500">{{ $calendar['period_label'] }}</p>
    <table class="erp-table w-full text-sm">
        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Asset / Machine') }}</th><th>{{ __('Item') }}</th><th>{{ __('Status') }}</th><th>{{ __('Priority') }}</th></tr></thead>
        <tbody>
            @forelse ($calendar['entries'] as $entry)
                <tr>
                    <td>{{ $entry['due_date'] ?? '—' }}</td>
                    <td>{{ $entry['asset_name'] ?? '—' }}</td>
                    <td>@if (! empty($entry['url']))<a href="{{ $entry['url'] }}" class="erp-link">{{ $entry['label'] }}</a>@else{{ $entry['label'] }}@endif</td>
                    <td>{{ $entry['status'] ?? '—' }}</td>
                    <td>{{ $entry['priority'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No scheduled maintenance in this period.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</x-admin.card>
