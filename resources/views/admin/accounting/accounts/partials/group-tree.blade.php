<ul @class(['space-y-1', 'ml-4 border-l border-erp-border pl-3' => $depth > 0])>
    @foreach ($nodes as $node)
        <li class="text-[11px] text-slate-600">
            <span class="font-mono text-slate-400">{{ $node['group']->code }}</span>
            {{ $node['group']->name }}
            @if ($node['children'] !== [])
                @include('admin.accounting.accounts.partials.group-tree', ['nodes' => $node['children'], 'depth' => $depth + 1])
            @endif
        </li>
    @endforeach
</ul>
