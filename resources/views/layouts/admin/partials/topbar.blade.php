<header id="erp-topbar" class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-erp-border bg-erp-card px-4 sm:gap-4 sm:px-6">
    <button
        type="button"
        class="rounded-lg p-2 text-slate-500 hover:bg-erp-page lg:hidden"
        @click="toggleMobileNav()"
        aria-label="{{ __('Open menu') }}"
    >
        <x-admin.icon name="menu" class="w-5 h-5" />
    </button>

    <div class="min-w-0 flex-1 lg:max-w-xs">
        <h1 id="erp-page-title" class="truncate text-base font-semibold text-erp-primary sm:text-lg">{{ $title ?? __('Admin') }}</h1>
    </div>

    <div class="hidden flex-1 justify-center px-4 md:flex">
        <div class="relative w-full max-w-xl">
            <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 w-4 h-4 -translate-y-1/2 text-slate-400" />
            <input
                type="search"
                placeholder="{{ __('Search customers, orders, jobs…') }}"
                class="erp-input w-full py-2 pl-9 pr-4"
                aria-label="{{ __('Global search') }}"
                disabled
                title="{{ __('Global search coming soon') }}"
            />
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
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

        @if (Route::has('client.login'))
            <a
                href="{{ route('client.login') }}"
                target="_blank"
                rel="noopener noreferrer"
                data-turbo="false"
                class="erp-btn erp-btn--secondary erp-btn--sm hidden items-center gap-1.5 lg:inline-flex"
                title="{{ __('Open client login') }}"
            >
                <x-admin.icon name="external-link" class="h-4 w-4 shrink-0" />
                <span>{{ __('Client Login') }}</span>
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
