<x-admin-layout :title="$return->return_number" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Returns'), 'url' => route('admin.commercial.pos.returns.dashboard')], ['label' => $return->return_number]]">
    <x-admin.page-header :title="$return->return_number" :description="$return->return_type->label()">
        <x-slot name="actions">
            <a href="{{ route('admin.commercial.pos.show', $return->sale) }}" class="erp-btn-secondary">{{ __('View Original Sale') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Return Summary') }}</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.enum-status-badge :status="$return->status->value" /></dd></div>
                <div><dt class="text-slate-500">{{ __('Refund Method') }}</dt><dd>{{ $return->refund_method->label() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Refund Amount') }}</dt><dd class="tabular-nums font-semibold">{{ number_format($return->refund_amount, 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Refund Reference') }}</dt><dd>{{ $return->refund_reference ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Created By') }}</dt><dd>{{ $return->creator?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Approved By') }}</dt><dd>{{ $return->approver?->name ?? '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-slate-500">{{ __('Reason') }}</dt><dd>{{ $return->reason }}</dd></div>
                @if ($return->rejection_reason)
                    <div class="col-span-2"><dt class="text-slate-500">{{ __('Rejection Reason') }}</dt><dd class="text-red-700">{{ $return->rejection_reason }}</dd></div>
                @endif
            </dl>

            @if ($canApprove)
                <div class="mt-4 flex flex-wrap gap-2 border-t border-erp-border pt-4">
                    <form method="POST" action="{{ route('admin.commercial.pos.returns.approve', $return) }}">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Approve Return') }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.commercial.pos.returns.reject', $return) }}" class="flex flex-1 flex-wrap items-end gap-2">
                        @csrf
                        <div class="min-w-[12rem] flex-1">
                            <label class="mb-1 block text-xs text-slate-500">{{ __('Rejection reason') }}</label>
                            <input type="text" name="rejection_reason" class="erp-input w-full" required>
                        </div>
                        <button type="submit" class="erp-btn-secondary text-red-700">{{ __('Reject') }}</button>
                    </form>
                </div>
            @endif
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Original Sale') }}</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Sale #') }}</dt><dd class="font-medium">{{ $return->sale->sale_number }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Sale Date') }}</dt><dd>{{ $return->sale->sale_date->format('Y-m-d') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Cashier') }}</dt><dd>{{ $return->sale->cashier?->name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Sale Total') }}</dt><dd class="tabular-nums">{{ number_format($return->sale->total_amount, 2) }}</dd></div>
            </dl>
        </x-admin.card>
    </div>

    <x-admin.card class="mb-6">
        <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Returned Items') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-slate-500">
                        <th class="py-2 pr-4">{{ __('Description') }}</th>
                        <th class="py-2 pr-4">{{ __('Returned Qty') }}</th>
                        <th class="py-2 pr-4">{{ __('Unit Price') }}</th>
                        <th class="py-2 pr-4">{{ __('Refund') }}</th>
                        <th class="py-2">{{ __('Reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($return->items as $item)
                        <tr class="border-b border-erp-border/60">
                            <td class="py-2 pr-4">{{ $item->description }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ $item->quantity_returned }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2 pr-4 tabular-nums">{{ number_format($item->line_refund_amount, 2) }}</td>
                            <td class="py-2">{{ $item->reason ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>

    @if ($canAudit)
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Audit Trail') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($return->events as $event)
                    <li class="border-b border-erp-border/60 py-2">
                        <span class="font-medium">{{ ucfirst($event->action) }}</span>
                        @if ($event->actor)
                            <span class="text-slate-500"> — {{ $event->actor->name }}</span>
                        @endif
                        <span class="text-slate-500"> — {{ $event->created_at?->format('d M Y H:i') }}</span>
                        @if ($event->notes)
                            <p class="mt-1 text-slate-600">{{ $event->notes }}</p>
                        @endif
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No audit entries yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>
