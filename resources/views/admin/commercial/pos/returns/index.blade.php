<x-admin-layout :title="__('Return History')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Returns'), 'url' => route('admin.commercial.pos.returns.dashboard')], ['label' => __('History')]]">
    <x-admin.page-header :title="__('Return History')" :description="__('All POS return transactions and reversal trail.')">
        <x-slot name="actions">
            @can('create', App\Models\Pos\PosReturn::class)
                <a href="{{ route('admin.commercial.pos.returns.create') }}" class="erp-btn-primary">{{ __('Create Return') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ __('Status') }}</label>
                <select name="status" class="erp-input">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ __('Return Type') }}</label>
                <select name="return_type" class="erp-input">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($returnTypes as $type)
                        <option value="{{ $type->value }}" @selected(($filters['return_type'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ __('Date') }}</label>
                <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="erp-input">
            </div>
            <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-slate-500">
                        <th class="py-2 pr-4">{{ __('Return #') }}</th>
                        <th class="py-2 pr-4">{{ __('Sale') }}</th>
                        <th class="py-2 pr-4">{{ __('Type') }}</th>
                        <th class="py-2 pr-4">{{ __('Status') }}</th>
                        <th class="py-2 pr-4">{{ __('Refund') }}</th>
                        <th class="py-2 pr-4">{{ __('Created By') }}</th>
                        <th class="py-2">{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $return)
                        <tr class="border-b border-erp-border/60">
                            <td class="py-2 pr-4">
                                <a href="{{ route('admin.commercial.pos.returns.show', $return) }}" class="font-medium text-erp-accent">{{ $return->return_number }}</a>
                            </td>
                            <td class="py-2 pr-4">{{ $return->sale?->sale_number }}</td>
                            <td class="py-2 pr-4">{{ $return->return_type->label() }}</td>
                            <td class="py-2 pr-4"><x-admin.enum-status-badge :status="$return->status->value" /></td>
                            <td class="py-2 pr-4 tabular-nums">{{ number_format($return->refund_amount, 2) }}</td>
                            <td class="py-2 pr-4">{{ $return->creator?->name }}</td>
                            <td class="py-2">{{ $return->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-4 text-slate-500">{{ __('No returns found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $returns->links() }}</div>
    </x-admin.card>
</x-admin-layout>
