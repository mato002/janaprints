<nav class="overflow-x-auto" aria-label="{{ __('Specification tabs') }}">
    <div class="flex min-w-max gap-1 border-b border-erp-border">
        @foreach ($tabs as $tab)
            <a
                href="{{ $tab['url'] }}"
                @class([
                    'inline-flex min-h-[2.5rem] items-center border-b-2 px-3 text-sm font-medium whitespace-nowrap',
                    'border-erp-accent text-erp-accent' => $tab['active'],
                    'border-transparent text-slate-600 hover:text-slate-900' => ! $tab['active'],
                ])
            >{{ $tab['label'] }}</a>
        @endforeach
    </div>
</nav>
