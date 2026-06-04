@props([
    'title',
    'description' => null,
    'backUrl' => null,
    'backLabel' => __('Settings'),
])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0">
        @if ($backUrl)
            <a
                href="{{ $backUrl }}"
                data-turbo-action="advance"
                class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-erp-primary transition-colors hover:text-erp-accent"
            >
                <x-admin.icon name="chevron-left" class="h-4 w-4" />
                {{ $backLabel }}
            </a>
        @endif
        <h1 class="text-xl font-semibold tracking-tight text-erp-primary sm:text-2xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
