<nav class="mb-4 flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('Operational registers') }}">
    @foreach ($registers as $key => $register)
        @php
            $query = array_merge($filters, ['register' => $key]);
            if (request('embedded')) {
                $query['embedded'] = '1';
            }
        @endphp
        <a
            href="{{ route('admin.reports.operational-registers', $query) }}"
            @class([
                'shrink-0 rounded-full border px-3 py-1.5 text-sm font-medium transition-colors',
                'border-erp-primary bg-erp-primary/10 text-erp-primary' => $active_register === $key,
                'border-erp-border bg-white text-slate-600 hover:border-erp-primary/40 hover:text-erp-primary' => $active_register !== $key,
            ])
            data-turbo-frame="{{ $turbo_frame }}"
        >
            {{ $register['label'] }}
        </a>
    @endforeach
</nav>
