@php
    use App\Support\Navigation\WorkspaceEmbed;

    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $historyLinks = [
        ['route' => 'timeline', 'icon' => 'clock', 'label' => __('Timeline'), 'theme' => 'history'],
        ['route' => 'communications', 'icon' => 'document-text', 'label' => __('Communications'), 'theme' => 'materials'],
        ['route' => 'artwork', 'icon' => 'photograph', 'label' => __('Attachments'), 'theme' => 'qc'],
    ];
@endphp

<x-admin.job-module-card theme="history" :title="__('History & records')" icon="clock" compact>
    <div class="grid grid-cols-3 gap-2">
        @foreach ($historyLinks as $link)
            <a
                href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => $link['route']]) }}"
                class="job-360-history-tile job-360-history-tile--{{ $link['theme'] }}"
                @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
            >
                <x-admin.icon :name="$link['icon']" class="job-360-history-tile__icon h-5 w-5" />
                <span class="job-360-history-tile__label">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>
</x-admin.job-module-card>
