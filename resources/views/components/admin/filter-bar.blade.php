<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 border-b border-erp-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="flex flex-1 flex-wrap items-center gap-2">
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
