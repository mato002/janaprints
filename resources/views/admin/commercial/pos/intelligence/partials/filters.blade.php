@props(['filters', 'branches', 'cashiers'])

@php
    use App\Enums\PosPaymentMethod;
    use App\Enums\PosSaleStatus;
@endphp

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar
        :action="route('commercial.pos.reports.index')"
        :reset-url="route('commercial.pos.reports.index')"
        :show-reset="false"
        turbo-frame="erp-main"
    >
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'sales_by_cashier' }}">
        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <select id="cashier_id" name="cashier_id" class="erp-toolbar-select" aria-label="{{ __('Cashier') }}">
            <option value="">{{ __('All cashiers') }}</option>
            @foreach ($cashiers as $cashier)
                <option value="{{ $cashier->id }}" @selected(($filters['cashier_id'] ?? null) == $cashier->id)>{{ $cashier->name }}</option>
            @endforeach
        </select>
        <select id="payment_method" name="payment_method" class="erp-toolbar-select" aria-label="{{ __('Payment method') }}">
            <option value="">{{ __('All payment methods') }}</option>
            @foreach (PosPaymentMethod::cases() as $method)
                <option value="{{ $method->value }}" @selected(($filters['payment_method'] ?? '') === $method->value)>{{ ucfirst($method->value) }}</option>
            @endforeach
        </select>

        <x-slot name="actions">
            <select id="status" name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (PosSaleStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                @endforeach
            </select>
            <a
                href="{{ route('commercial.pos.reports.index') }}"
                class="erp-btn-ghost shrink-0 py-1 text-xs text-slate-500"
                data-turbo-frame="erp-main"
            >{{ __('Reset') }}</a>
        </x-slot>

        <x-slot name="secondary">
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="{{ __('Sale number…') }}"
                class="erp-toolbar-input w-full min-w-0 flex-1"
                data-erp-auto-search
                aria-label="{{ __('Search') }}"
            >
        </x-slot>
    </x-admin.index-toolbar>
</x-admin.card>
