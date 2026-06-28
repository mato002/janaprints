@props(['summary'])

@if (! empty($summary))
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
        @foreach ($summary as $item)
            <x-admin.kpi-widget
                :label="$item['label']"
                :value="$item['value']"
                :icon="$item['icon'] ?? 'chart-bar'"
            />
        @endforeach
    </div>
@endif
