@php
    $description = $salesOrder->order_number.' — '.__('Remaining billable').': '.number_format($salesOrder->remainingInvoiceTotal(), 2);
@endphp

<x-admin.modal-form
    :title="__('Create invoice')"
    :breadcrumbs="[
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
        ['label' => __('Create invoice')],
    ]"
    maxWidth="2xl"
>
    @unless (request()->header('Turbo-Frame') === 'erp-form-modal')
        <x-admin.page-header :title="__('Create invoice')" :description="$description" />
    @else
        <p class="mb-4 text-sm text-slate-600">{{ $description }}</p>
    @endunless

    @if ($pendingInvoices->isNotEmpty())
        <div class="mb-4 rounded-lg border border-erp-border bg-slate-50 px-4 py-3">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Existing invoices') }}</p>
            <ul class="space-y-1 text-sm">
                @foreach ($pendingInvoices as $pendingInvoice)
                    <li>
                        <a href="{{ route('admin.invoices.show', $pendingInvoice) }}" class="font-mono text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $pendingInvoice->invoice_number }}</a>
                        <span class="text-slate-500"> — {{ $pendingInvoice->invoice_type->label() }} {{ number_format($pendingInvoice->total_amount, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.invoices.store-from-sales-order', $salesOrder) }}"
        class="space-y-4"
        x-data="{
            billingType: '{{ old('invoice_type', 'standard') }}',
            eligibility: @js($billingEligibilityByType),
            selectedEligibility() { return this.eligibility[this.billingType] ?? { eligible: true, blockers: [] }; }
        }"
    >
        @csrf
        @include('admin.partials.modal-validation-alert')

        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" x-show="! selectedEligibility().eligible" x-cloak>
            <p class="mb-2 font-medium text-amber-950">{{ __('This billing type is blocked') }}</p>
            <ul class="list-disc ps-5">
                <template x-for="blocker in selectedEligibility().blockers" :key="blocker">
                    <li x-text="blocker"></li>
                </template>
            </ul>
            @if ($salesOrder->jobCard)
                <p class="mt-3">
                    <a href="{{ route('admin.production.job-cards.show', $salesOrder->jobCard) }}" class="font-medium text-erp-accent underline" data-turbo-frame="erp-main">{{ __('Open production job') }}</a>
                    {{ __('to post finished goods, or complete dispatch/collection on the job.') }}
                </p>
            @endif
            <p class="mt-2" x-show="eligibility.deposit?.eligible || eligibility.progress?.eligible">
                {{ __('To bill before production finishes, switch billing type to Deposit or Progress billing.') }}
            </p>
        </div>

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
                <input type="date" name="due_date" value="{{ old('due_date', $salesOrder->payment_terms_days ? now()->addDays((int) $salesOrder->payment_terms_days)->toDateString() : '') }}" min="{{ now()->toDateString() }}" class="erp-input w-full">
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

        <div>
            <label class="erp-label">{{ __('Notes') }}</label>
            <textarea name="notes" rows="2" class="erp-input w-full">{{ old('notes') }}</textarea>
        </div>

        <div class="rounded-lg border border-erp-border p-3" x-show="billingType === 'partial'" x-cloak>
            <h3 class="mb-3 text-sm font-medium">{{ __('Lines') }}</h3>
            @foreach ($salesOrder->items as $index => $item)
                <div class="flex flex-wrap items-center gap-3 border-t border-erp-border py-2 text-sm">
                    <input type="checkbox" name="lines[{{ $index }}][selected]" value="1" class="rounded">
                    <input type="hidden" name="lines[{{ $index }}][sales_order_item_id]" value="{{ $item->id }}">
                    <span class="flex-1 font-medium">{{ $item->item_name }}</span>
                    <span class="text-slate-500">{{ __('Max') }} {{ $item->quantity }}</span>
                    <input type="number" name="lines[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="0.001" step="0.001" class="erp-input w-24">
                </div>
            @endforeach
        </div>

        <x-admin.form-modal-actions>
            <button
                type="submit"
                class="erp-btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="! selectedEligibility().eligible"
                :title="selectedEligibility().eligible ? '' : selectedEligibility().blockers.join(' ')"
            >{{ __('Create invoice') }}</button>
        </x-admin.form-modal-actions>

        <p class="text-sm text-slate-500" x-show="! selectedEligibility().eligible" x-cloak>
            {{ __('Create invoice is disabled until the blockers above are resolved, or you choose a billing type that is allowed at this stage.') }}
        </p>
    </form>
</x-admin.modal-form>
