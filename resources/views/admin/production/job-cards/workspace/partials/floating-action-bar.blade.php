@php
    use App\Support\Navigation\WorkspaceEmbed;

    $primaryAction = $primaryAction ?? null;
    $linkActions = $linkActions ?? [];
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $formTurboAttrs = WorkspaceEmbed::mainFormAttributes();

    $printAction = collect($linkActions)->first(fn ($link) => ($link['target'] ?? null) === '_blank');

    $actions = [];

    if ($primaryAction) {
        $isOperationsLink = str_contains((string) ($primaryAction['url'] ?? ''), 'tab=operations');
        if (! ($isOperationsLink && ($activeTab ?? null) === 'operations')) {
            $actions[] = $primaryAction;
        }
    }
    if ($printAction) {
        $actions[] = [
            'label' => $printAction['label'],
            'type' => 'link',
            'url' => $printAction['url'],
            'target' => '_blank',
            'variant' => 'secondary',
        ];
    }
@endphp

@if (! empty($actions))
    <div class="job-360-fab" aria-label="{{ __('Quick actions') }}">
        <div class="job-360-fab__inner">
            @foreach ($actions as $action)
                @if (($action['type'] ?? '') === 'anchor')
                    <a href="{{ $action['url'] }}" class="job-360-fab__btn job-360-fab__btn--{{ $action['variant'] ?? 'primary' }}">
                        {{ $action['label'] }}
                    </a>
                @elseif (($action['type'] ?? '') === 'post')
                    <form method="POST" action="{{ $action['url'] }}" class="job-360-fab__form" @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                        @csrf
                        <button type="submit" class="job-360-fab__btn job-360-fab__btn--{{ $action['variant'] ?? 'primary' }}">
                            {{ $action['label'] }}
                        </button>
                    </form>
                @elseif (($action['type'] ?? '') === 'link')
                    <a
                        href="{{ $action['url'] }}"
                        class="job-360-fab__btn job-360-fab__btn--{{ $action['variant'] ?? 'secondary' }}"
                        @if (($action['target'] ?? null) === '_blank') target="_blank" rel="noopener" @else @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach @endif
                    >{{ $action['label'] }}</a>
                @endif
            @endforeach
        </div>
    </div>
@endif
