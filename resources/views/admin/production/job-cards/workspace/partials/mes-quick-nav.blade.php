@php
    use App\Support\Navigation\WorkspaceEmbed;

    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $links = [
        ['tab' => 'quality', 'label' => __('QC'), 'theme' => 'qc'],
        ['tab' => 'dispatch', 'label' => __('Disp'), 'theme' => 'dispatch'],
        ['tab' => 'timeline', 'label' => __('Time'), 'theme' => 'history'],
        ['tab' => 'operations', 'label' => __('Ops'), 'theme' => 'production'],
    ];
@endphp

<div class="mes-quick-nav" aria-label="{{ __('Quick navigation') }}">
    @foreach ($links as $link)
        <a
            href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => $link['tab']]) }}"
            class="mes-kpi mes-kpi--{{ $link['theme'] }} mes-kpi--link"
            @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
        >
            <span class="mes-kpi__label">{{ $link['label'] }}</span>
            <span class="mes-kpi__value mes-kpi__value--arrow">→</span>
        </a>
    @endforeach
</div>
