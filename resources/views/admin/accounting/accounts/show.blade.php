<x-admin-layout :title="$account->name" :breadcrumbs="[['label' => __('Chart of Accounts'), 'url' => route('admin.accounting.accounts.index')], ['label' => $account->code]]">
    <x-admin.page-header :title="$account->name" :description="$account->code">
        <x-admin.status-badge :variant="match($account->status) {
            App\Enums\GlAccountStatus::Active => 'success',
            App\Enums\GlAccountStatus::Inactive => 'neutral',
            App\Enums\GlAccountStatus::Locked => 'warning',
        }">{{ $account->status->label() }}</x-admin.status-badge>
        @if ($account->is_system)<span class="erp-badge">{{ __('System') }}</span>@endif
        @can('update', $account)
            <a href="{{ route('admin.accounting.accounts.edit', $account) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endcan
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ $account->accountType->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Group') }}</dt><dd>{{ $account->accountGroup?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Parent') }}</dt><dd>
                    @if ($account->parent)
                        <a href="{{ route('admin.accounting.accounts.show', $account->parent) }}" class="text-erp-accent">{{ $account->parent->code }} — {{ $account->parent->name }}</a>
                    @else — @endif
                </dd></div>
                <div><dt class="text-slate-500">{{ __('Branch scope') }}</dt><dd>{{ $account->branch?->name ?? __('Company-wide') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Normal balance') }}</dt><dd>{{ $account->normal_balance->label() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Postable') }}</dt><dd>{{ $account->is_postable ? __('Yes') : __('No (header)') }}</dd></div>
                @if ($account->description)
                    <div><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $account->description }}</dd></div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Child accounts') }}</h3>
            @forelse ($account->children as $child)
                <div class="mb-2 flex justify-between text-sm">
                    <a href="{{ route('admin.accounting.accounts.show', $child) }}" class="text-erp-accent">{{ $child->code }} — {{ $child->name }}</a>
                    <span class="text-slate-400">{{ $child->status->label() }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('No child accounts.') }}</p>
                @can('create', App\Models\Accounting\GlAccount::class)
                    <a href="{{ route('admin.accounting.accounts.create', ['parent_id' => $account->id, 'type_id' => $account->gl_account_type_id]) }}" class="mt-2 inline-block text-sm text-erp-accent">{{ __('Add child account') }}</a>
                @endcan
            @endforelse
        </x-admin.card>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
        @can('lock', $account)
            @if ($account->status !== App\Enums\GlAccountStatus::Locked)
                <form method="POST" action="{{ route('admin.accounting.accounts.lock', $account) }}">@csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Lock account') }}</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.accounting.accounts.unlock', $account) }}">@csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Unlock account') }}</button>
                </form>
            @endif
        @endcan
        @can('delete', $account)
            <form method="POST" action="{{ route('admin.accounting.accounts.destroy', $account) }}" onsubmit="return confirm(@js(__('Delete this account?')))">
                @csrf @method('DELETE')
                <button type="submit" class="erp-btn-secondary text-red-600">{{ __('Delete') }}</button>
            </form>
        @endcan
    </div>
</x-admin-layout>
