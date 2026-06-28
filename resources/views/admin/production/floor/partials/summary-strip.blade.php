<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
    @foreach ($summary as $card)
        <a
            href="{{ route('admin.production.floor', $card['filter'] ?? []) }}"
            class="block transition-opacity hover:opacity-90"
            data-turbo-frame="module-workspace-content"
        >
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" />
        </a>
    @endforeach
</div>
