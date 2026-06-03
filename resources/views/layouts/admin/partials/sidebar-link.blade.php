@php
    use App\Providers\AppServiceProvider as Nav;
    $active = Nav::navItemIsActive($child);
    $comingSoon = ! empty($child['coming_soon']);
@endphp

@if ($comingSoon)
    <span
        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed {{ ! empty($collapsed) ? 'lg:justify-center lg:px-2' : 'pl-9' }}"
        title="{{ __('Coming soon') }}"
    >
        @if (! empty($child['icon']))
            <x-admin.icon :name="$child['icon']" class="w-4 h-4 shrink-0 opacity-50" />
        @endif
        <span @if (! empty($collapsed)) x-cloak @endif class="{{ ! empty($collapsed) ? 'sr-only lg:not-sr-only lg:hidden' : '' }}">{{ $child['label'] }}</span>
        @if (empty($collapsed))
            <span class="ml-auto text-[10px] uppercase tracking-wide text-slate-600">{{ __('Soon') }}</span>
        @endif
    </span>
@else
    <a
        href="{{ route($child['route']) }}"
        data-turbo-frame="erp-main"
        data-turbo-action="advance"
        data-nav-route="{{ $child['route'] }}"
        data-nav-depth="child"
        @click="$dispatch('close-nav')"
        class="flex items-center gap-3 rounded-lg py-2 text-sm font-medium transition-colors {{ $active ? 'bg-erp-accent/90 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }} {{ ! empty($collapsed) ? 'lg:justify-center lg:px-2 px-3' : 'px-3 pl-9' }}"
        title="{{ $child['label'] }}"
    >
        @if (! empty($child['icon']))
            <x-admin.icon :name="$child['icon']" class="w-4 h-4 shrink-0" />
        @endif
        <span class="{{ ! empty($collapsed) ? 'sr-only' : 'truncate' }}">{{ $child['label'] }}</span>
    </a>
@endif
