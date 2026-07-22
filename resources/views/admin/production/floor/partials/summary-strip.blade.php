@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp
<div class="production-floor-board mb-4 grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
    @foreach ($summary as $card)
        <a
            href="{{ WorkspaceEmbed::url(route('admin.production.floor', $card['filter'] ?? [])) }}"
            class="block transition-opacity hover:opacity-90"
            data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
            data-turbo-action="advance"
        >
            <x-admin.kpi-widget
                :label="strtoupper($card['label'])"
                :value="$card['value']"
                :hint="$card['hint'] ?? null"
                class="production-floor-board__card"
            />
        </a>
    @endforeach
</div>
