@props([
    'customer',
    'latestOrderForRepeat' => null,
    'buttonClass' => 'crm-360__btn crm-360__btn--outline',
    'buttonLabel' => null,
    'omitPrimary' => false,
])

<div class="relative crm-360__more" x-data="{ open: false }">
    <button
        type="button"
        class="{{ $buttonClass }}"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="true"
    >
        {{ $buttonLabel ?? __('Actions') }}
        <svg class="h-4 w-4 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div
        x-show="open"
        x-cloak
        @click.outside="open = false"
        class="crm-360__more-menu"
        role="menu"
    >
        @include('admin.crm.customers.360.partials.customer-actions-menu-items', [
            'customer' => $customer,
            'latestOrderForRepeat' => $latestOrderForRepeat,
            'omitPrimary' => $omitPrimary,
        ])
    </div>
</div>
