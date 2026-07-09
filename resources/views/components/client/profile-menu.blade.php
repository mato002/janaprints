@props([
    'compact' => false,
    'variant' => 'default',
])

@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('');
    $isTopbar = $variant === 'topbar';
@endphp

<div @class(['client-profile-menu', 'client-profile-menu--topbar' => $isTopbar]) data-client-profile-menu>
    <button
        type="button"
        class="client-profile-menu__trigger"
        data-client-profile-toggle
        aria-expanded="false"
        aria-haspopup="true"
        aria-controls="client-profile-dropdown"
        aria-label="{{ __('Open account menu') }}"
    >
        <span class="client-profile-menu__avatar" aria-hidden="true">{{ $initials ?: 'C' }}</span>
        @unless ($compact || $isTopbar)
            <span class="client-profile-menu__trigger-text">
                <span class="client-profile-menu__name">{{ $user->name }}</span>
                <span class="client-profile-menu__company">{{ $user->customer?->company_name }}</span>
            </span>
            <svg class="client-profile-menu__chevron h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        @endunless
        @if ($isTopbar)
            <svg class="client-profile-menu__chevron client-profile-menu__chevron--topbar h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        @endif
    </button>

    @if ($isTopbar)
        <div class="client-profile-menu__backdrop" data-client-profile-backdrop hidden aria-hidden="true"></div>
    @endif

    <div
        id="client-profile-dropdown"
        class="client-profile-menu__dropdown"
        data-client-profile-dropdown
        role="menu"
        hidden
    >
        <div class="client-profile-menu__header">
            <p class="client-profile-menu__header-name">{{ $user->name }}</p>
            <p class="client-profile-menu__header-company">{{ $user->customer?->company_name }}</p>
        </div>
        <div class="client-profile-menu__items">
            <a href="{{ route('client.account.edit') }}" class="client-profile-menu__item" role="menuitem" data-turbo-frame="client-main" data-turbo-action="advance">
                <x-client.icon name="user" class="h-4 w-4" />
                {{ __('Account settings') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" data-turbo-frame="_top">
                @csrf
                <button type="submit" class="client-profile-menu__item client-profile-menu__item--danger" role="menuitem">
                    <x-client.icon name="logout" class="h-4 w-4" />
                    {{ __('Sign out') }}
                </button>
            </form>
        </div>
    </div>
</div>
