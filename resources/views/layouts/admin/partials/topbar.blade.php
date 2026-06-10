<header id="erp-topbar" class="z-30 flex h-16 min-w-0 shrink-0 items-center gap-2 overflow-hidden border-b border-erp-border bg-erp-card px-3 sm:gap-3 sm:px-4 lg:px-6">
    <button
        type="button"
        class="shrink-0 rounded-lg p-2 text-slate-500 hover:bg-erp-page lg:hidden"
        @click="toggleMobileNav()"
        aria-label="{{ __('Open menu') }}"
    >
        <x-admin.icon name="menu" class="w-5 h-5" />
    </button>

    <div class="hidden min-w-0 max-w-[7rem] shrink sm:block md:max-w-[9rem] lg:max-w-xs xl:max-w-sm">
        <h1 id="erp-page-title" class="truncate text-sm font-semibold text-erp-primary sm:text-base lg:text-lg">{{ $title ?? __('Admin') }}</h1>
    </div>

    <div class="hidden min-w-0 flex-1 md:block">
        <button
            type="button"
            class="relative flex w-full min-w-0 max-w-xl items-center rounded-lg border border-erp-border bg-erp-page py-2 pl-9 pr-14 text-left text-sm text-slate-500 transition-colors hover:border-erp-accent/40 hover:bg-white lg:mx-auto lg:max-w-2xl xl:pr-16"
            @click="openPalette()"
            aria-label="{{ __('Open feature finder') }}"
        >
            <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <span class="truncate lg:hidden">{{ __('Search features…') }}</span>
            <span class="hidden truncate lg:inline">{{ __('Search customers, jobs, reports, settings, features…') }}</span>
            <span class="pointer-events-none absolute right-2 top-1/2 hidden -translate-y-1/2 items-center gap-1 xl:flex">
                <kbd class="rounded border border-erp-border bg-erp-card px-1.5 py-0.5 text-[10px] font-medium text-slate-400">Ctrl</kbd>
                <kbd class="rounded border border-erp-border bg-erp-card px-1.5 py-0.5 text-[10px] font-medium text-slate-400">K</kbd>
            </span>
        </button>
    </div>

    <button
        type="button"
        class="shrink-0 rounded-lg p-2 text-slate-500 hover:bg-erp-page md:hidden"
        @click="openPalette()"
        aria-label="{{ __('Open feature finder') }}"
    >
        <x-admin.icon name="search" class="h-5 w-5" />
    </button>

    <div class="ml-auto flex shrink-0 items-center gap-1.5 sm:gap-2 lg:gap-3">
        @php
            $companies = auth()->user()->hasRole('Super Admin')
                ? \App\Models\Company::query()->where('is_active', true)->orderBy('name')->get()
                : \App\Models\Company::query()->where('id', auth()->user()->company_id)->get();
            $branches = tenant()->company
                ? tenant()->company->branches()->where('is_active', true)->orderBy('name')->get()
                : collect();
        @endphp

        @if ($companies->isNotEmpty())
            <form method="POST" action="{{ route('admin.context.update') }}" data-turbo-frame="_top" class="hidden items-center gap-2 sm:flex">
                @csrf
                <select name="company_id" onchange="this.form.submit()" class="erp-select max-w-[9rem] py-1.5 text-xs sm:max-w-[11rem] sm:text-sm" aria-label="{{ __('Company') }}">
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(tenant()->companyId() === $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
                @if ($branches->isNotEmpty())
                    <select name="branch_id" onchange="this.form.submit()" class="erp-select max-w-[9rem] py-1.5 text-xs sm:max-w-[11rem] sm:text-sm" aria-label="{{ __('Branch') }}">
                        <option value="">{{ __('All branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(tenant()->branchId() === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                @endif
            </form>
        @endif

        <div id="erp-quick-create" class="contents">
            @include('layouts.admin.partials.quick-create', ['quickCreate' => $quickCreate ?? []])
        </div>

        @include('layouts.admin.partials.quote-requests-topbar')

        @if (Route::has('home'))
            <a
                href="{{ url('/') }}"
                target="_blank"
                rel="noopener noreferrer"
                data-turbo="false"
                class="erp-btn erp-btn--secondary erp-btn--sm inline-flex items-center gap-1.5"
                title="{{ __('View public website') }}"
            >
                <x-admin.icon name="external-link" class="h-4 w-4 shrink-0" />
                <span class="hidden sm:inline">{{ __('Website') }}</span>
            </a>
        @endif

        @include('layouts.admin.partials.notification-bell')

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-700 hover:bg-erp-page">
                    @if (! empty($userAvatarUrl))
                        <img src="{{ $userAvatarUrl }}" alt="" class="hidden h-8 w-8 rounded-full border border-erp-border object-cover sm:block">
                    @else
                        <span class="hidden h-8 w-8 items-center justify-center rounded-full bg-erp-accent/10 text-xs font-semibold text-erp-accent sm:flex">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </span>
                    @endif
                    <span class="hidden max-w-[8rem] truncate md:inline">{{ auth()->user()->name }}</span>
                    <x-admin.icon name="chevron-down" class="w-4 h-4 text-slate-400" />
                </button>
            </x-slot>
            <x-slot name="content">
                <div class="border-b border-erp-border px-4 py-2 md:hidden">
                    <p class="text-xs text-slate-500">{{ tenant()->company?->name }}</p>
                    <p class="text-sm font-medium text-erp-primary">{{ auth()->user()->name }}</p>
                </div>
                <x-dropdown-link :href="route('profile.edit')" data-turbo-frame="_top">{{ __('Profile') }}</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}" data-turbo-frame="_top">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
