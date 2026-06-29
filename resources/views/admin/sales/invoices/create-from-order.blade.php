<x-admin-layout :title="__('Invoice from order')" :breadcrumbs="[
    ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
    ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
    ['label' => __('Create invoice')],
]">
    <x-admin.page-header :title="__('Create invoice')" :description="$salesOrder->order_number.' — '.__('Remaining billable').': '.number_format($salesOrder->remainingInvoiceTotal(), 2)" />

    @if ($pendingInvoices->isNotEmpty())
        <x-admin.card class="mb-4">
            <ul class="text-sm">
                @foreach ($pendingInvoices as $pendingInvoice)
                    <li>
                        <a href="{{ route('admin.invoices.show', $pendingInvoice) }}" class="text-erp-accent font-mono">{{ $pendingInvoice->invoice_number }}</a>
                        <span class="text-slate-500"> — {{ $pendingInvoice->invoice_type->label() }} {{ number_format($pendingInvoice->total_amount, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <form method="POST" action="{{ route('admin.invoices.store-from-sales-order', $salesOrder) }}" class="space-y-6 max-w-2xl" x-data="{
        billingType: '{{ old('invoice_type', 'standard') }}',
        eligibility: @js($billingEligibilityByType),
        selectedEligibility() { return this.eligibility[this.billingType] ?? { eligible: true, blockers: [] }; }
    }">
        @csrf

        <x-admin.card class="border-amber-200 bg-amber-50" x-show="! selectedEligibility().eligible" x-cloak>
            <p class="mb-2 text-sm font-medium text-amber-950">{{ __('This billing type is blocked') }}</p>
            <ul class="list-disc ps-5 text-sm text-amber-900">
                <template x-for="blocker in selectedEligibility().blockers" :key="blocker">
                    <li x-text="blocker"></li>
                </template>
            </ul>
            @if ($salesOrder->jobCard)
                <p class="mt-3 text-sm text-amber-900">
                    <a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}" class="font-medium text-erp-accent underline" data-turbo-frame="_top">{{ __('Open production job') }}</a>
                    {{ __('to post finished goods, or complete dispatch/collection on the job.') }}
                </p>
            @endif
            <p class="mt-2 text-sm text-amber-900" x-show="eligibility.deposit?.eligible || eligibility.progress?.eligible">
                {{ __('To bill before production finishes, switch billing type to Deposit or Progress billing.') }}
            </p>
        </x-admin.card>

        <x-admin.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="erp-label">{{ __('Billing type') }}</label>
                    <select name="invoice_type" class="erp-input w-full" x-model="billingType" required>
                        <option value="standard">{{ __('Full invoice') }}</option>
                        <option value="partial">{{ __('Partial (selected lines)') }}</option>
                        <option value="deposit">{{ __('Deposit') }}</option>
                        <option value="progress">{{ __('Progress billing') }}</option>
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Invoice date') }}</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', now()->toDateString()) }}" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Due date') }}</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $salesOrder->payment_terms_days ? now()->addDays((int) $salesOrder->payment_terms_days)->toDateString() : '') }}" class="erp-input w-full">
                </div>
                <div x-show="billingType === 'progress'" x-cloak>
                    <label class="erp-label">{{ __('Progress %') }}</label>
                    <input type="number" name="billing_percent" min="1" max="100" step="0.01" class="erp-input w-full" placeholder="30" :required="billingType === 'progress'">
                </div>
                <div x-show="billingType === 'deposit'" x-cloak>
                    <label class="erp-label">{{ __('Deposit amount') }}</label>
                    <input type="number" name="deposit_amount" min="0.01" step="0.01" class="erp-input w-full" :required="billingType === 'deposit'">
                </div>
            </div>
            <div class="mt-4">
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="erp-input w-full">{{ old('notes') }}</textarea>
            </div>
        </x-admin.card>

        <x-admin.card x-show="billingType === 'partial'" x-cloak>
            <h3 class="font-medium mb-3">{{ __('Lines') }}</h3>
            @foreach ($salesOrder->items as $index => $item)
                <div class="flex flex-wrap items-center gap-3 py-2 border-t border-erp-border text-sm">
                    <input type="checkbox" name="lines[{{ $index }}][selected]" value="1" class="rounded">
                    <input type="hidden" name="lines[{{ $index }}][sales_order_item_id]" value="{{ $item->id }}">
                    <span class="flex-1 font-medium">{{ $item->item_name }}</span>
                    <span class="text-slate-500">{{ __('Max') }} {{ $item->quantity }}</span>
                    <input type="number" name="lines[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="0.001" step="0.001" class="erp-input w-24">
                </div>
            @endforeach
        </x-admin.card>

        <div class="space-y-2">
            <button
                type="submit"
                class="erp-btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="! selectedEligibility().eligible"
                :title="selectedEligibility().eligible ? '' : selectedEligibility().blockers.join(' ')"
            >{{ __('Create invoice') }}</button>
            <p class="text-sm text-slate-500" x-show="! selectedEligibility().eligible" x-cloak>
                {{ __('Create invoice is disabled until the blockers above are resolved, or you choose a billing type that is allowed at this stage.') }}
            </p>
        </div>
    </form>
</x-admin-layout>
