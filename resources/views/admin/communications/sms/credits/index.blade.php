<x-admin-layout :title="__('SMS Credit Ledger')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Credit ledger')]]">
    @include('admin.communications.sms.partials.nav')
    <x-admin.page-header :title="__('SMS credit ledger')" />

    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-5">
        <x-admin.stat-card :label="__('Opening')" :value="number_format($balance->opening_credits, 0)" />
        <x-admin.stat-card :label="__('Purchased')" :value="number_format($balance->purchased_credits, 0)" />
        <x-admin.stat-card :label="__('Used')" :value="number_format($balance->used_credits, 0)" />
        <x-admin.stat-card :label="__('Remaining')" :value="number_format($balance->remaining_credits, 0)" />
        <x-admin.stat-card :label="__('Cost / segment')" :value="number_format($balance->cost_per_sms, 2)" />
    </div>

    @can('audit', App\Models\Communications\SmsCampaign::class)
        <form method="POST" action="{{ route('admin.communications.sms.credits.purchase') }}" class="erp-card mb-4 flex flex-wrap items-end gap-2" data-turbo-frame="erp-main">
            @csrf
            <div>
                <label class="erp-label text-xs">{{ __('Purchase credits') }}</label>
                <input type="number" name="credits" class="erp-input" min="1" step="1" required>
            </div>
            <button type="submit" class="erp-btn erp-btn--primary">{{ __('Add credits') }}</button>
        </form>
    @endcan

    <div class="erp-card overflow-hidden p-0">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Balance after') }}</th>
                    <th>{{ __('Campaign') }}</th>
                    <th>{{ __('Branch') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $tx)
                    <tr>
                        <td class="text-xs">{{ $tx->created_at?->format('d M Y H:i') }}</td>
                        <td>{{ $tx->transaction_type->label() }}</td>
                        <td class="tabular-nums {{ $tx->amount < 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($tx->amount, 2) }}</td>
                        <td class="tabular-nums">{{ number_format($tx->balance_after, 2) }}</td>
                        <td>{{ $tx->campaign?->name ?? '—' }}</td>
                        <td>{{ $tx->branch?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No transactions yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($transactions->hasPages())
            <div class="border-t px-4 py-3">{{ $transactions->links() }}</div>
        @endif
    </div>
</x-admin-layout>
