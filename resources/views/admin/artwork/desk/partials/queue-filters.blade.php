@if (count($filters ?? []) > 0)
    <div class="mb-3 flex flex-wrap items-center gap-1.5" role="toolbar" aria-label="{{ __('Queue filters') }}">
        @foreach ($filters as $filter)
            <button
                type="button"
                @class([
                    'inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold transition',
                ])
                :class="(activeFilter === @js($filter['key']) || (@js($filter['key']) === 'all' && !activeFilter))
                    ? 'border-erp-accent bg-erp-accent text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                @click="setFilter(@js($filter['key']))"
            >{{ $filter['label'] }}</button>
        @endforeach
    </div>
@endif
