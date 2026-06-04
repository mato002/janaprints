@props([
    'icon' => 'inbox',
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-16 text-center']) }} data-export-skip>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-erp-page text-slate-400">
        <x-admin.icon :name="$icon" class="w-7 h-7" />
    </div>
    <h3 class="text-base font-semibold text-erp-primary">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endisset
</div>
