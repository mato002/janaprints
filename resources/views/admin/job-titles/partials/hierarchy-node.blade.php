<div class="rounded-lg border border-erp-border bg-white p-4" @style(['margin-left: '.($depth * 1.5).'rem'])>
    <div>
        <div class="font-semibold text-erp-primary">{{ $node['title'] }}</div>
        <div class="mt-1 font-mono text-[11px] text-slate-500">{{ $node['code'] }}</div>
        <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
            <span>{{ $node['level'] }}</span>
            @if ($node['department'])
                <span>· {{ $node['department'] }}</span>
            @endif
            <span>· {{ trans_choice(':count employee|:count employees', $node['employee_count'], ['count' => $node['employee_count']]) }}</span>
            @if ($node['approval_authority'])
                <span>· {{ __('Approval') }}: {{ $node['approval_authority'] }}</span>
            @endif
        </div>
    </div>

    @if (! empty($node['children']))
        <div class="mt-3 space-y-3 border-l-2 border-erp-accent/20 pl-4">
            @foreach ($node['children'] as $child)
                @include('admin.job-titles.partials.hierarchy-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
