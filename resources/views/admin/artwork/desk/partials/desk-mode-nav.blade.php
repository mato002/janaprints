@php
    use App\Support\Artwork\DesignerDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = DesignerDeskViews::normalize(request('filter', DesignerDeskViews::QUEUE));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => DesignerDeskViews::QUEUE,
            'label' => __('Queue'),
            'url' => DesignerDeskViews::deskUrl(DesignerDeskViews::QUEUE),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::AVAILABLE,
            'label' => __('Available'),
            'url' => DesignerDeskViews::availableUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::MINE,
            'label' => __('Mine'),
            'url' => DesignerDeskViews::mineUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::WORKING,
            'label' => __('Working'),
            'url' => DesignerDeskViews::workingUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
        [
            'key' => DesignerDeskViews::REVIEW,
            'label' => __('Review'),
            'url' => DesignerDeskViews::reviewUrl(),
            'visible' => $user?->can('viewAny', \App\Models\Artwork\ArtworkRequest::class) ?? false,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <div class="designer-desk-ribbon mb-3 shrink-0">
        <nav class="designer-desk-ribbon__tabs" aria-label="{{ __('Designer desk modes') }}">
            @foreach ($modes as $mode)
                <a
                    href="{{ WorkspaceEmbed::url($mode['url']) }}"
                    @class([
                        'designer-desk-ribbon__tab',
                        'designer-desk-ribbon__tab--'.$mode['key'] => filled($mode['key'] ?? null),
                        'designer-desk-ribbon__tab--active' => $mode['key'] === $active,
                    ])
                    data-turbo-frame="{{ $frame }}"
                    data-turbo-action="advance"
                >{{ $mode['label'] }}</a>
            @endforeach
        </nav>
    </div>
@endif
