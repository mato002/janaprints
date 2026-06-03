@php
    $companies = auth()->user()->hasRole('Super Admin')
        ? \App\Models\Company::query()->where('is_active', true)->orderBy('name')->get()
        : \App\Models\Company::query()->where('id', auth()->user()->company_id)->get();
    $branches = tenant()->company
        ? tenant()->company->branches()->where('is_active', true)->orderBy('name')->get()
        : collect();

    $quickCreate = array_values(array_filter([
        ['label' => __('Quote'), 'coming_soon' => true],
        ['label' => __('Customer'), 'route' => 'admin.crm.customers.create', 'model' => App\Models\Crm\Customer::class],
        ['label' => __('Job Card'), 'coming_soon' => true],
        ['label' => __('Invoice'), 'coming_soon' => true],
    ], function ($item) {
        if (! empty($item['coming_soon'])) {
            return true;
        }
        if (! empty($item['model'])) {
            return auth()->user()?->can('create', $item['model']);
        }

        return true;
    }));
@endphp

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

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="erp-btn-primary py-2">
                    <x-admin.icon name="plus" class="w-4 h-4" />
                    <span class="hidden sm:inline">{{ __('Create') }}</span>
                </button>
            </x-slot>
            <x-slot name="content">
                @foreach ($quickCreate as $item)
                    @if (! empty($item['coming_soon']))
                        <span class="block w-full px-4 py-2 text-start text-sm leading-5 text-slate-400 cursor-not-allowed">{{ $item['label'] }} <span class="text-xs">({{ __('Soon') }})</span></span>
                    @elseif (! empty($item['route']) && (! empty($item['model']) ? auth()->user()?->can('create', $item['model']) : true))
                        <x-dropdown-link :href="route($item['route'])">{{ $item['label'] }}</x-dropdown-link>
                    @endif
                @endforeach
            </x-slot>
        </x-dropdown>

        <button type="button" class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-erp-page hover:text-slate-700" title="{{ __('Notifications') }}" aria-label="{{ __('Notifications') }}">
            <x-admin.icon name="bell" class="w-5 h-5" />
            <span class="absolute right-1.5 top-1.5 block h-2 w-2 rounded-full bg-erp-warning ring-2 ring-white" title="{{ __('Coming soon') }}"></span>
        </button>

        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-700 hover:bg-erp-page">
                    <span class="hidden h-8 w-8 items-center justify-center rounded-full bg-erp-accent/10 text-xs font-semibold text-erp-accent sm:flex">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </span>
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
