<x-admin-layout :title="__('Cash Reconciliation History')">
    <x-admin.page-header :title="__('Cash Reconciliation History')" :description="__('Approved and rejected reconciliations.')">
        <x-slot name="actions">
            <a href="{{ route('admin.commercial.pos.reconciliation.index') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-6">
        <x-admin.index-toolbar :action="route('admin.commercial.pos.reconciliation.history')" :reset-url="route('admin.commercial.pos.reconciliation.history')">
            <x-admin.status-pills
                :options="[['value' => '', 'label' => __('All')], ['value' => 'approved', 'label' => __('Approved')], ['value' => 'rejected', 'label' => __('Rejected')]]"
                param="status"
                :current="$filters['status'] ?? ''"
            />
            @if ($branches->isNotEmpty())
                <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                    <option value="">{{ __('All branches') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2">{{ __('Reconciliation') }}</th>
                        <th class="px-3 py-2">{{ __('Session') }}</th>
                        <th class="px-3 py-2">{{ __('Cashier') }}</th>
                        <th class="px-3 py-2">{{ __('Variance') }}</th>
                        <th class="px-3 py-2">{{ __('Status') }}</th>
                        <th class="px-3 py-2">{{ __('Resolved') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reconciliations as $reconciliation)
                        <tr class="border-b border-erp-border/60">
                            <td class="px-3 py-2">
                                <a href="{{ route('admin.commercial.pos.reconciliation.show', $reconciliation) }}" class="font-medium text-erp-accent">{{ $reconciliation->reconciliation_number }}</a>
                            </td>
                            <td class="px-3 py-2">{{ $reconciliation->session?->session_number ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $reconciliation->cashier?->name ?? '—' }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ number_format($reconciliation->variance, 2) }} ({{ ucfirst($reconciliation->variance_type->value) }})</td>
                            <td class="px-3 py-2">{{ ucfirst($reconciliation->status->value) }}</td>
                            <td class="px-3 py-2">{{ ($reconciliation->approved_at ?? $reconciliation->rejected_at)?->format('d M Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ __('No reconciliation history found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reconciliations->links() }}</div>
    </x-admin.card>
</x-admin-layout>
