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
            :title="sidebarCollapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
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
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-erp-accent text-sm font-bold">JP</span>
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

    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2 py-4">
        @foreach ($navItems as $index => $item)
            @if (isset($item['children']))
                @php
                    $groupId = 'nav-'.Str::slug($item['label']);
                    $isOpen = Nav::navGroupIsOpen($item);
                @endphp
                @php
                    $groupRoutes = collect($item['children'] ?? [])
                        ->pluck('route')
                        ->filter()
                        ->implode(',');
                @endphp
                <div
                    x-data="navGroup('{{ $groupId }}', {{ $isOpen ? 'true' : 'false' }})"
                    data-nav-group
                    data-nav-group-routes="{{ $groupRoutes }}"
                    data-nav-group-open="{{ $isOpen ? '1' : '0' }}"
                    class="mb-1"
                >
                    <button
                        type="button"
                        @click="toggle()"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-white/5 hover:text-white"
                        :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                        :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''"
                    >
                        <x-admin.icon :name="$item['icon'] ?? 'home'" class="h-5 w-5 shrink-0 text-slate-400" />
                        <span class="flex-1 truncate text-left" x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                        <x-admin.icon
                            name="chevron-down"
                            class="h-4 w-4 shrink-0 text-slate-500 transition-transform duration-200"
                            x-show="!sidebarCollapsed"
                            x-cloak
                            ::class="open ? 'rotate-180' : ''"
                        />
                    </button>
                    <div
                        x-show="open && !sidebarCollapsed"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        x-cloak
                        class="mt-0.5 space-y-0.5 pl-3"
                    >
                        @foreach ($item['children'] as $child)
                            @include('layouts.admin.partials.sidebar-link', ['child' => $child])
                        @endforeach
                    </div>
                    <div x-show="sidebarCollapsed" x-cloak class="mt-0.5 hidden space-y-0.5 lg:block">
                        @foreach ($item['children'] as $child)
                            @if (empty($child['coming_soon']) && ! empty($child['route']))
                                @include('layouts.admin.partials.sidebar-link', ['child' => $child, 'collapsed' => true])
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                @php $active = Nav::navItemIsActive($item); @endphp
                <a
                    href="{{ route($item['route']) }}"
                    data-turbo-frame="erp-main"
                    data-turbo-action="advance"
                    data-nav-route="{{ $item['route'] }}"
                    @click="$dispatch('close-nav')"
                    class="mb-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $active ? 'bg-erp-accent text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}"
                    :class="sidebarCollapsed ? 'lg:justify-center lg:px-2' : ''"
                    title="{{ $item['label'] }}"
                >
                    <x-admin.icon :name="$item['icon'] ?? 'home'" class="h-5 w-5 shrink-0" />
                    <span x-show="!sidebarCollapsed" x-cloak>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</aside>
