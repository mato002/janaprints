@php
    use App\Support\Commercial\PosDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    $active = PosDeskViews::normalize($activePosView ?? request('view', PosDeskViews::COUNTER));
    $inWorkspace = WorkspaceEmbed::inWorkspaceContext();
    $frame = $inWorkspace ? WorkspaceEmbed::turboFrame() : 'erp-main';
    $user = auth()->user();
    $modes = collect([
        [
            'key' => PosDeskViews::COUNTER,
            'label' => __('Counter'),
            'url' => PosDeskViews::counterUrl(),
            'visible' => ($user?->can('pos.view') || $user?->can('pos.counter_sales.view') || $user?->can('viewAny', \App\Models\Pos\PosSale::class)) ?? false,
        ],
        [
            'key' => PosDeskViews::SALES,
            'label' => __('Sales'),
            'url' => route('admin.commercial.pos.index'),
            'visible' => $user?->can('viewAny', \App\Models\Pos\PosSale::class) ?? false,
        ],
        [
            'key' => PosDeskViews::SESSIONS,
            'label' => __('Sessions'),
            'url' => route('admin.commercial.pos.sessions.index'),
            'visible' => $user?->can('commercial.pos.sessions.view') ?? false,
        ],
        [
            'key' => PosDeskViews::RETURNS,
            'label' => __('Returns'),
            'url' => route('admin.commercial.pos.returns.dashboard'),
            'visible' => $user?->can('commercial.pos.returns.view') ?? false,
        ],
        [
            'key' => PosDeskViews::RECON,
            'label' => __('Cash recon'),
            'url' => route('admin.commercial.pos.reconciliation.index'),
            'visible' => $user?->can('commercial.pos.reconciliation.view') ?? false,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <nav class="mb-4 flex flex-wrap gap-1.5" aria-label="{{ __('POS desk modes') }}">
        @foreach ($modes as $mode)
            <a
                href="{{ $mode['url'] }}"
                @class([
                    'erp-filter-pill',
                    'erp-filter-pill--active' => $mode['key'] === $active,
                ])
                data-turbo-frame="{{ $frame }}"
            >{{ $mode['label'] }}</a>
        @endforeach
    </nav>
@endif
