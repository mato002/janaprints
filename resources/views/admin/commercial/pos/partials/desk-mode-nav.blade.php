@php
    use App\Support\Commercial\PosDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = PosDeskViews::normalize($activePosView ?? request('view', PosDeskViews::COUNTER));
    $frame = WorkspaceEmbed::turboFrame();
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
    <nav class="workspace-context-tabs" aria-label="{{ __('POS desk modes') }}">
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
