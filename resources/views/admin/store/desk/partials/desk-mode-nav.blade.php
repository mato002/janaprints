@php
    use App\Support\Inventory\StoreDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = StoreDeskViews::normalize($activeStoreView ?? request('view', StoreDeskViews::DESK));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => StoreDeskViews::DESK,
            'label' => __('Desk'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::DESK),
            'visible' => $user?->can('inventory.view') ?? false,
        ],
        [
            'key' => StoreDeskViews::PRODUCTS,
            'label' => __('Products'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::PRODUCTS),
            'visible' => $user?->can('catalogue.view') || ($user?->can('inventory.view') ?? false),
        ],
        [
            'key' => StoreDeskViews::BALANCES,
            'label' => __('Balances'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::BALANCES),
            'visible' => $user?->can('inventory.view') ?? false,
        ],
        [
            'key' => StoreDeskViews::RECEIPTS,
            'label' => __('Receipts'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::RECEIPTS),
            'visible' => $user?->can('inventory.view') ?? false,
        ],
        [
            'key' => StoreDeskViews::ISSUES,
            'label' => __('Issues'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::ISSUES),
            'visible' => $user?->can('inventory.view') ?? false,
        ],
        [
            'key' => StoreDeskViews::TRANSFERS,
            'label' => __('Transfers'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::TRANSFERS),
            'visible' => $user?->can('inventory.view') ?? false,
        ],
        [
            'key' => StoreDeskViews::ADJUSTMENTS,
            'label' => __('Adjustments'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::ADJUSTMENTS),
            'visible' => $user?->can('inventory.view') ?? false,
        ],
        [
            'key' => StoreDeskViews::MOVEMENTS,
            'label' => __('Movements'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::MOVEMENTS),
            'visible' => $user?->can('inventory.view') ?? false,
        ],
        [
            'key' => StoreDeskViews::ALERTS,
            'label' => __('Alerts'),
            'url' => StoreDeskViews::deskUrl(StoreDeskViews::ALERTS),
            'visible' => ($user?->can('inventory.reorder.view') || $user?->can('inventory.view')) ?? false,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <nav class="workspace-context-tabs" aria-label="{{ __('Store desk modes') }}">
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
