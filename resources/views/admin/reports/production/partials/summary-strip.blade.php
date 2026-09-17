@props(['summary'])

@if (! empty($summary))
    <x-admin.collapsible-summary>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
            @foreach ($summary as $item)
                <x-admin.kpi-widget
                    :label="$item['label']"
                    :value="$item['value']"
                    :icon="$item['icon'] ?? 'chart-bar'"
                />
            @endforeach
        </div>
    </x-admin.collapsible-summary>
@endif
