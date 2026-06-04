<nav class="c360-tabs" aria-label="{{ __('Job workspace tabs') }}">
    @foreach ($tabs as $tab)
        <a
            href="{{ $tab['url'] }}"
            class="c360-tabs__link {{ $tab['active'] ? 'c360-tabs__link--active' : '' }}"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            @if ($tab['active']) aria-current="page" @endif
        >{{ $tab['label'] }}</a>
    @endforeach
</nav>
