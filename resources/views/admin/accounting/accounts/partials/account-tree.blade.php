<ul @class(['space-y-1', 'ml-4 border-l border-erp-border pl-3' => $depth > 0])>
    @foreach ($nodes as $node)
        @php $account = $node['account']; @endphp
        <li>
            <div class="flex flex-wrap items-center gap-2 rounded-md py-1 pr-2 text-sm hover:bg-erp-page/80">
                <a href="{{ route('admin.accounting.accounts.show', $account) }}" class="font-mono text-[11px] text-erp-accent hover:text-erp-accent-hover">
                    {{ $account->code }}
                </a>
                <a href="{{ route('admin.accounting.accounts.show', $account) }}" class="font-medium text-erp-primary hover:text-erp-accent">
                    {{ $account->name }}
                </a>
                <span class="text-[10px] text-slate-400">{{ $account->normal_balance->label() }}</span>
                @if (! $account->is_postable)
                    <span class="erp-badge text-[10px]">{{ __('Header') }}</span>
                @endif
                @if ($account->is_system)
                    <span class="erp-badge text-[10px]">{{ __('System') }}</span>
                @endif
                <x-admin.status-badge :variant="match($account->status) {
                    App\Enums\GlAccountStatus::Active => 'success',
                    App\Enums\GlAccountStatus::Inactive => 'neutral',
                    App\Enums\GlAccountStatus::Locked => 'warning',
                }">{{ $account->status->label() }}</x-admin.status-badge>
                @can('create', App\Models\Accounting\GlAccount::class)
                    <a href="{{ route('admin.accounting.accounts.create', ['parent_id' => $account->id, 'type_id' => $account->gl_account_type_id]) }}" class="text-[10px] text-erp-accent">{{ __('Add child') }}</a>
                @endcan
            </div>
            @if ($node['children'] !== [])
                @include('admin.accounting.accounts.partials.account-tree', ['nodes' => $node['children'], 'depth' => $depth + 1])
            @endif
        </li>
    @endforeach
</ul>
