@php
    use App\Support\Navigation\WorkspaceEmbed;

    $groups = $workspace['tab_groups'] ?? ['primary' => $tabs, 'more' => [], 'more_open' => false];
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
@endphp

<div class="c360-tabs-shell" aria-label="{{ __('Job workspace tabs') }}">
    <nav class="c360-tabs c360-tabs--compact c360-tabs--stretch c360-tabs--scroll">
        @foreach ($groups['primary'] as $tab)
            <a
                href="{{ $tab['url'] }}"
                class="c360-tabs__link {{ $tab['active'] ? 'c360-tabs__link--active' : '' }}"
                @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                @if ($tab['active']) aria-current="page" @endif
            >{{ $tab['label'] }}</a>
        @endforeach
    </nav>

    @if (! empty($groups['more']))
        <details class="c360-tabs__more" @if ($groups['more_open'] ?? false) open @endif>
            <summary class="c360-tabs__link c360-tabs__link--more flex h-full cursor-pointer list-none items-center [&::-webkit-details-marker]:hidden {{ collect($groups['more'])->contains('active', true) ? 'c360-tabs__link--active' : '' }}">
                {{ __('More') }}
            </summary>
            <div class="c360-tabs__more-menu" role="menu">
                @foreach ($groups['more'] as $tab)
                    <a
                        href="{{ $tab['url'] }}"
                        class="c360-tabs__more-link {{ $tab['active'] ? 'c360-tabs__more-link--active' : '' }}"
                        role="menuitem"
                        @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                    >{{ $tab['label'] }}</a>
                @endforeach
            </div>
        </details>
    @endif
</div>
