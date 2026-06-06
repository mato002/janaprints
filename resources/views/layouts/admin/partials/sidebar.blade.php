@php
    use App\Providers\AppServiceProvider as Nav;
@endphp

<aside
    id="erp-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-erp-sidebar text-slate-200 transition-all duration-sidebar -translate-x-full lg:translate-x-0"
    :class="[
        mobileNavOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarCollapsed ? 'w-sidebar-collapsed' : 'w-sidebar',
    ]"
    aria-label="{{ __('Main navigation') }}"
>
    <div
        class="flex h-16 shrink-0 items-center gap-2 border-b border-white/10 px-3 lg:px-4"
        :class="sidebarCollapsed ? 'lg:h-auto lg:flex-col lg:justify-center lg:gap-2 lg:py-3 lg:px-2' : ''"
    >
        <button
            type="button"
            @click="toggleSidebar()"
            class="hidden shrink-0 rounded-lg p-2 text-slate-400 transition-colors hover:bg-white/10 hover:text-white lg:inline-flex"
            :class="sidebarCollapsed ? 'lg:order-1' : 'lg:order-none'"
            :aria-label="sidebarCollapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
        >
            <x-admin.icon name="chevron-left" class="h-5 w-5 transition-transform duration-sidebar" ::class="sidebarCollapsed ? 'rotate-180' : ''" />
        </button>

        <a
            href="{{ route('admin.dashboard') }}"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            class="flex min-w-0 flex-1 items-center gap-3 font-semibold tracking-tight text-white"
            :class="sidebarCollapsed ? 'lg:order-2 lg:flex-none lg:justify-center' : ''"
            @click="$dispatch('close-nav')"
        >
            <img
                src="{{ $brandingSidebarLogoUrl }}"
                alt=""
                class="h-9 w-9 shrink-0 rounded-lg object-contain bg-white"
                width="36"
                height="36"
                decoding="async"
                aria-hidden="true"
            >
            <span class="truncate text-base" x-show="!sidebarCollapsed" x-cloak>{{ config('app.name') }}</span>
        </a>

        <button
            type="button"
            class="ml-auto shrink-0 rounded-lg p-2 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden"
            @click="closeMobileNav()"
            aria-label="{{ __('Close menu') }}"
        >
            <x-admin.icon name="chevron-left" class="h-5 w-5 rotate-180" />
        </button>
    </div>

    <div x-show="!sidebarCollapsed" x-cloak class="shrink-0 space-y-2 border-b border-white/10 px-3 py-3">
        <label class="sr-only" for="nav-search">{{ __('Search navigation') }}</label>
        <div class="relative">
            <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
            <input
                id="nav-search"
                type="search"
                x-model="query"
                @focus="searchOpen = true"
                @keydown.escape="clearSearch()"
                class="w-full rounded-lg border-0 bg-white/10 py-2 pl-8 pr-3 text-sm text-white placeholder:text-slate-500 focus:bg-white/15 focus:ring-2 focus:ring-erp-accent/40"
                placeholder="{{ __('Search features…') }}"
                autocomplete="off"
            >
        </div>

        <div x-show="searchOpen && query.trim()" x-cloak class="max-h-48 overflow-y-auto rounded-lg border border-white/10 bg-erp-primary/90 shadow-lg">
            <template x-for="hit in searchHits" :key="hit.path">
                <a
                    x-show="! hit.coming_soon"
                    :href="hit.url"
                    data-turbo-frame="erp-main"
                    data-turbo-action="advance"
                    @click="clearSearch(); $dispatch('close-nav')"
                    class="block border-b border-white/5 px-3 py-2 text-sm text-slate-200 last:border-0 hover:bg-white/10"
                >
                    <span class="block font-medium" x-text="hit.label"></span>
                    <span class="block text-xs text-slate-500" x-text="hit.path"></span>
                </a>
            </template>
            <p x-show="searchHits.length === 0" class="px-3 py-4 text-center text-xs text-slate-500">{{ __('No matches') }}</p>
        </div>

        <div x-show="favoriteItems.length > 0 && !query.trim()" x-cloak>
            <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Pinned') }}</p>
            <div class="flex flex-wrap gap-1">
                <template x-for="fav in favoriteItems" :key="fav.route">
                    <a
                        :href="fav.url"
                        data-turbo-frame="erp-main"
                        class="inline-flex items-center gap-1 rounded-md bg-white/10 px-2 py-1 text-xs text-slate-200 hover:bg-erp-primary hover:text-white"
                        :title="fav.path"
                        @click="$dispatch('close-nav')"
                    >
                        <span x-text="fav.label"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2 py-3" x-show="!query.trim() || sidebarCollapsed">
        @foreach ($navItems as $item)
            @php $active = Nav::navItemIsActive($item); @endphp
            <a
                href="{{ route($item['route']) }}"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
                data-nav-route="{{ $item['route'] }}"
                @if (! empty($item['active_routes']))
                    data-nav-active-routes="{{ implode(',', $item['active_routes']) }}"
                @endif
                @click="$dispatch('close-nav')"
                class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors erp-nav-link {{ $active ? 'erp-nav-link--active border-l-3 border-erp-accent bg-erp-primary text-white' : 'text-slate-200 hover:text-white' }}"
                :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                title="{{ $item['label'] }}"
            >
                <x-admin.icon :name="$item['icon'] ?? 'home'" class="h-5 w-5 shrink-0" />
                <span class="truncate" x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                @if (! empty($item['badge_count']) && (int) $item['badge_count'] > 0)
                    <span class="erp-nav-badge erp-nav-badge--quote" x-show="!sidebarCollapsed" x-cloak>{{ $item['badge_count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</aside>
