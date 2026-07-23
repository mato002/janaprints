@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<nav class="mb-4 flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('Department queues') }}">
    @foreach ($departmentNav as $item)
        <a
            href="{{ WorkspaceEmbed::url($item['url']) }}"
            @class([
                'shrink-0 rounded-full border px-3 py-1.5 text-sm font-medium transition-colors',
                'border-erp-primary bg-erp-primary/10 text-erp-primary' => $item['active'],
                'border-erp-border bg-white text-slate-600 hover:border-erp-primary/40 hover:text-erp-primary' => ! $item['active'],
            ])
            data-turbo-frame="{{ WorkspaceEmbed::turboFrame() }}"
            data-turbo-action="advance"
        >
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>
