@props([
    'items' => [],
])

<div {{ $attributes->merge(['class' => 'health-summary-strip grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6']) }} role="list" aria-label="{{ __('System health summary') }}">
    @foreach ($items as $item)
        <x-admin.health.health-status-card
            role="listitem"
            :label="$item['label']"
            :status="$item['status']"
            :value="$item['value']"
            :detail="$item['detail'] ?? null"
            :icon="$item['icon'] ?? null"
        />
    @endforeach
</div>
