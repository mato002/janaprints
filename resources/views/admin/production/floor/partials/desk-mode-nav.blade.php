@php
    use App\Models\Production\ProductionJobCard;
    use App\Models\Production\ProductionOutput;
    use App\Models\Production\ProductionQueue;
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Production\ProductionFloorDeskViews;

    if (WorkspaceEmbed::inWorkspaceContext()) {
        return;
    }

    $active = ProductionFloorDeskViews::normalize($activeFloorView ?? request('view'));
    $frame = WorkspaceEmbed::turboFrame();
    $user = auth()->user();
    $modes = collect([
        [
            'key' => ProductionFloorDeskViews::FLOOR,
            'label' => __('Run'),
            'url' => ProductionFloorDeskViews::floorUrl(ProductionFloorDeskViews::FLOOR),
            'visible' => $user?->can('viewAny', ProductionJobCard::class) ?? false,
        ],
        [
            'key' => ProductionFloorDeskViews::REGISTER,
            'label' => __('Register'),
            'url' => ProductionFloorDeskViews::registerIndexUrl(),
            'visible' => $user?->can('viewAny', ProductionJobCard::class) ?? false,
        ],
        [
            'key' => ProductionFloorDeskViews::QUEUE,
            'label' => __('By department'),
            'url' => ProductionFloorDeskViews::queueIndexUrl(),
            'visible' => $user?->can('viewWorkspace', ProductionQueue::class) ?? false,
        ],
        [
            'key' => ProductionFloorDeskViews::OUTPUTS,
            'label' => __('Outputs'),
            'url' => ProductionFloorDeskViews::outputsIndexUrl(),
            'visible' => $user?->can('viewAny', ProductionOutput::class) ?? false,
        ],
    ])->where('visible', true)->values();
@endphp

@if ($modes->count() > 1)
    <nav class="workspace-context-tabs" aria-label="{{ __('Production floor modes') }}">
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
