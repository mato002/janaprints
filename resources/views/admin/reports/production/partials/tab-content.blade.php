@props(['tab_data', 'active_tab', 'print_mode' => false])

@if (($tab_data['type'] ?? '') === 'placeholder')
    @unless ($print_mode)
        <x-admin.card>
            <x-admin.empty-state
                icon="chart-bar"
                :title="__('Production Reports')"
                :description="$tab_data['message'] ?? __('No data available for the selected filters.')"
            />
        </x-admin.card>
    @endunless
@else
    @unless ($print_mode)
        @include('admin.reports.production.partials.summary-strip', ['summary' => $tab_data['summary'] ?? []])
    @else
        <div class="summary">
            @foreach ($tab_data['summary'] ?? [] as $item)
                <div class="metric">
                    <p class="metric-label">{{ $item['label'] }}</p>
                    <p class="metric-value">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
    @endunless

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @foreach ($tab_data['sections'] ?? [] as $section)
            <div @class(['lg:col-span-2' => $section['full_width'] ?? false])>
                @include('admin.reports.production.partials.simple-table', $section['table'])
            </div>
        @endforeach
    </div>
@endif
