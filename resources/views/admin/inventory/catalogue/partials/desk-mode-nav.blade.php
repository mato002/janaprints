@php
    use App\Support\Inventory\CatalogueDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    $active = CatalogueDeskViews::normalize($activeCatalogueView ?? request('view', CatalogueDeskViews::PRODUCTS));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $canView = $user?->can('catalogue.view') ?? false;

    $modes = collect([
        [
            'key' => CatalogueDeskViews::PRODUCTS,
            'label' => __('Products'),
            'url' => CatalogueDeskViews::deskUrl(CatalogueDeskViews::PRODUCTS),
            'visible' => $canView,
        ],
        [
            'key' => CatalogueDeskViews::PRICE_LISTS,
            'label' => __('Price lists'),
            'url' => CatalogueDeskViews::deskUrl(CatalogueDeskViews::PRICE_LISTS),
            'visible' => $canView,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <nav class="workspace-context-tabs" aria-label="{{ __('Catalogue desk modes') }}">
        @foreach ($modes as $mode)
            <a
                href="{{ WorkspaceEmbed::url($mode['url']) }}"
                @class([
                    'workspace-context-tab',
                    'workspace-context-tab--active' => $mode['key'] === $active,
                ])
                data-turbo-frame="{{ $frame }}"
                data-turbo-action="advance"
            >{{ $mode['label'] }}</a>
        @endforeach
    </nav>
@endif
