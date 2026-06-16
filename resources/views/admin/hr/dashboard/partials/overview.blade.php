@props(['cards'])

@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<section class="mb-3" aria-label="{{ __('Workforce Overview') }}">
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-6">
        @foreach ($cards as $card)
            @if ($card['clickable'] ?? false)
                <a href="{{ \App\Support\Navigation\WorkspaceEmbed::url($card['url']) }}" class="block transition-opacity hover:opacity-90" data-turbo-frame="{{ $turboFrame }}">
                    <x-admin.kpi-widget
                        :label="$card['label']"
                        :value="$card['value']"
                        :icon="$card['icon']"
                        :trend="$card['trend']"
                    />
                </a>
            @else
                <x-admin.kpi-widget
                    :label="$card['label']"
                    :value="$card['value']"
                    :icon="$card['icon']"
                    :trend="$card['trend']"
                />
            @endif
        @endforeach
    </div>
</section>
