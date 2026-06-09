@props(['dimensions', 'active_dimension', 'filters'])

<div class="mb-4 flex flex-wrap gap-2 border-b border-erp-border pb-3">
    @foreach ($dimensions as $dimension)
        <a
            href="{{ route('admin.hr.kpi', array_merge($filters, ['dimension' => $dimension['key']])) }}"
            @class([
                'rounded-md px-3 py-1.5 text-sm font-medium transition',
                'bg-erp-primary text-white' => $active_dimension === $dimension['key'],
                'text-slate-600 hover:bg-slate-100' => $active_dimension !== $dimension['key'],
            ])
            data-turbo-frame="erp-main"
        >
            {{ $dimension['label'] }}
        </a>
    @endforeach
</div>
