<section class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
    @foreach ($items as $item)
        <article class="rounded border border-erp-border px-3 py-2">
            <p class="text-xs text-slate-500">{{ $item['label'] }}</p>
            <p class="truncate text-sm font-semibold tabular-nums text-slate-900">{{ $item['value'] }}</p>
        </article>
    @endforeach
</section>
