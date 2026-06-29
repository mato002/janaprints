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
        data-turbo-preload="hover"
        data-nav-route="{{ $child['route'] }}"
        @if (! empty($child['active_routes']))
            data-nav-active-routes="{{ implode(',', $child['active_routes']) }}"
        @endif
        data-nav-depth="child"
        @click="$dispatch('close-nav')"
        class="group/link erp-nav-link {{ $active ? 'erp-nav-link--active border-l-3 border-erp-accent bg-erp-primary text-white' : '' }} {{ ! empty($collapsed) ? 'lg:justify-center lg:px-2 px-3' : 'px-3' }} {{ empty($collapsed) && ! empty($indent) ? 'pl-6' : (empty($collapsed) ? 'pl-9' : '') }}"
        title="{{ $child['label'] }}"
    >
        @if (! empty($child['icon']))
            <x-admin.icon :name="$child['icon']" class="w-4 h-4 shrink-0" />
        @endif
        <span class="{{ ! empty($collapsed) ? 'sr-only' : 'truncate' }}">{{ $child['label'] }}</span>
        @if (empty($collapsed) && ! empty($child['route']))
            <button
                type="button"
                class="ml-auto hidden rounded p-0.5 text-slate-500 hover:text-amber-300 group-hover/link:inline-flex"
                :class="isFavorite('{{ $child['route'] }}') ? '!inline-flex text-amber-400' : ''"
                @click.prevent.stop="toggleFavorite('{{ $child['route'] }}')"
                :title="isFavorite('{{ $child['route'] }}') ? '{{ __('Unpin') }}' : '{{ __('Pin to favorites') }}'"
                aria-label="{{ __('Pin menu item') }}"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </button>
        @endif
    </a>
@endif
