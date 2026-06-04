@props([
    'align' => 'right',
])

<div
    class="relative inline-block text-left"
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        @click.stop="open = !open"
        class="erp-row-actions-trigger inline-flex h-8 w-8 items-center justify-center rounded-md border border-transparent text-slate-500 transition-colors hover:border-erp-border hover:bg-erp-page hover:text-erp-primary"
        :aria-expanded="open"
        aria-haspopup="true"
        aria-label="{{ __('Row actions') }}"
    >
        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
        </svg>
    </button>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        @click="open = false"
        class="erp-row-actions-menu absolute z-30 mt-1 min-w-[10rem] rounded-lg border border-erp-border bg-white py-1 shadow-lg {{ $align === 'right' ? 'end-0' : 'start-0' }}"
    >
        {{ $slot }}
    </div>
</div>
