<x-admin-layout :title="__('Cash Reconciliation Detail')">
    <x-admin.page-header :title="$reconciliation->reconciliation_number" :description="__('Session :session', ['session' => $reconciliation->session?->session_number ?? '—'])">
        <x-slot name="actions">
            <a href="{{ route('admin.commercial.pos.reconciliation.index') }}" class="erp-btn-secondary">{{ __('Back to dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 xl:grid-cols-5">
        <x-admin.kpi-widget :label="__('Opening Float')" :value="number_format($reconciliation->opening_float, 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('Cash Sales')" :value="number_format($reconciliation->cash_sales, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('M-Pesa Sales')" :value="number_format($reconciliation->mpesa_sales, 2)" icon="device-mobile" />
        <x-admin.kpi-widget :label="__('Card Sales')" :value="number_format($reconciliation->card_sales, 2)" icon="credit-card" />
        <x-admin.kpi-widget :label="__('Refunds')" :value="$reconciliation->refunds_count" icon="switch-horizontal" />
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-admin.card>
            <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Cash Summary') }}</h2>
            <dl class="grid gap-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Cashier') }}</dt><dd>{{ $reconciliation->cashier?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Branch') }}</dt><dd>{{ $reconciliation->branch?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Expected Cash') }}</dt><dd class="tabular-nums font-semibold">{{ number_format($reconciliation->expected_cash, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Actual Cash') }}</dt><dd class="tabular-nums font-semibold">{{ number_format($reconciliation->actual_cash, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Variance') }}</dt><dd class="tabular-nums font-semibold">{{ number_format($reconciliation->variance, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Variance Type') }}</dt><dd>{{ ucfirst($reconciliation->variance_type->value) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ ucfirst(str_replace('_', ' ', $reconciliation->status->value)) }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Approval Workflow') }}</h2>
            <dl class="grid gap-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Submitted') }}</dt><dd>{{ $reconciliation->submitted_at?->format('d M Y H:i') ?? '—' }} {{ $reconciliation->submitter?->name ? '· '.$reconciliation->submitter->name : '' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Reviewed') }}</dt><dd>{{ $reconciliation->reviewed_at?->format('d M Y H:i') ?? '—' }} {{ $reconciliation->reviewer?->name ? '· '.$reconciliation->reviewer->name : '' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Approved') }}</dt><dd>{{ $reconciliation->approved_at?->format('d M Y H:i') ?? '—' }} {{ $reconciliation->approver?->name ? '· '.$reconciliation->approver->name : '' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Rejected') }}</dt><dd>{{ $reconciliation->rejected_at?->format('d M Y H:i') ?? '—' }} {{ $reconciliation->rejector?->name ? '· '.$reconciliation->rejector->name : '' }}</dd></div>
            </dl>

            <div class="mt-6 space-y-4">
                @if ($can_submit && $reconciliation->status === App\Enums\PosReconciliationStatus::Pending)
                    <form method="POST" action="{{ route('admin.commercial.pos.reconciliation.submit', $reconciliation) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-[11px] text-slate-500" for="notes">{{ __('Submission Notes') }}</label>
                            <textarea id="notes" name="notes" rows="2" class="erp-input mt-1 w-full">{{ old('notes', $reconciliation->notes) }}</textarea>
                        </div>
                        <button type="submit" class="erp-btn-primary">{{ __('Submit reconciliation') }}</button>
                    </form>
                @endif

                @if ($can_review && $reconciliation->status->awaitsApproval() && $reconciliation->reviewed_at === null)
                    <form method="POST" action="{{ route('admin.commercial.pos.reconciliation.review', $reconciliation) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-[11px] text-slate-500" for="review_notes">{{ __('Supervisor Review Notes') }}</label>
                            <textarea id="review_notes" name="review_notes" rows="2" class="erp-input mt-1 w-full">{{ old('review_notes') }}</textarea>
                        </div>
                        <button type="submit" class="erp-btn-secondary">{{ __('Mark as reviewed') }}</button>
                    </form>
                @endif

                @if ($can_approve && $reconciliation->status->awaitsApproval() && $reconciliation->reviewed_at !== null)
                    <form method="POST" action="{{ route('admin.commercial.pos.reconciliation.approve', $reconciliation) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-[11px] text-slate-500" for="approval_notes">{{ __('Approval Notes') }}</label>
                            <textarea id="approval_notes" name="approval_notes" rows="2" class="erp-input mt-1 w-full">{{ old('approval_notes') }}</textarea>
                        </div>
                        <button type="submit" class="erp-btn-primary">{{ __('Approve reconciliation') }}</button>
                    </form>
                @endif

                @if ($can_reject && $reconciliation->status->awaitsApproval() && $reconciliation->reviewed_at !== null)
                    <form method="POST" action="{{ route('admin.commercial.pos.reconciliation.reject', $reconciliation) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-[11px] text-slate-500" for="rejection_reason">{{ __('Rejection Reason') }}</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="2" class="erp-input mt-1 w-full" required>{{ old('rejection_reason') }}</textarea>
                        </div>
                        <button type="submit" class="erp-btn-secondary text-rose-700">{{ __('Reject reconciliation') }}</button>
                    </form>
                @endif
            </div>
        </x-admin.card>
    </div>

    @if ($can_audit && $logs->isNotEmpty())
        <x-admin.card>
            <h2 class="mb-4 text-sm font-semibold text-erp-primary">{{ __('Audit Trail') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ __('When') }}</th>
                            <th class="px-3 py-2">{{ __('User') }}</th>
                            <th class="px-3 py-2">{{ __('Action') }}</th>
                            <th class="px-3 py-2">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="border-b border-erp-border/60">
                                <td class="px-3 py-2">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-3 py-2">{{ $log->user?->name ?? '—' }}</td>
                                <td class="px-3 py-2">{{ ucfirst($log->action->value) }}</td>
                                <td class="px-3 py-2">{{ $log->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif
</x-admin-layout>
