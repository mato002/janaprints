@php
    $turboFrame = request('embedded') ? 'module-workspace-content' : 'erp-main';
@endphp

<x-admin-layout :title="__('Customer deposits')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Customer deposits')]]">
    <x-admin.page-header :title="__('Customer deposits')" :description="__('Unallocated and applied customer deposit credits.')">
        @can('create', App\Models\Sales\CustomerPayment::class)
            <a href="{{ route('admin.payments.create', ['is_deposit' => 1]) }}" class="erp-btn-primary">{{ __('Record deposit') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Number') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Unallocated') }}</th>
                <th>{{ __('Status') }}</th>
                <th></th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($deposits as $deposit)
                @php
                    $showUrl = route('admin.payments.show', $deposit);
                    if (request('embedded')) {
                        $showUrl .= str_contains($showUrl, '?') ? '&embedded=1' : '?embedded=1';
                    }
                @endphp
                <tr>
                    <td class="font-mono text-erp-accent">
                        <a href="{{ $showUrl }}" data-turbo-frame="{{ $turboFrame }}">{{ $deposit->payment_number }}</a>
                    </td>
                    <td>{{ $deposit->customer?->company_name }}</td>
                    <td>{{ $deposit->payment_date->format('Y-m-d') }}</td>
                    <td class="font-mono">{{ number_format((float) $deposit->amount, 2) }}</td>
                    <td class="font-mono">{{ number_format((float) $deposit->unallocated_amount, 2) }}</td>
                    <td>{{ $deposit->status->label() }}</td>
                    <td class="text-right space-x-2">
                        @if ((float) $deposit->unallocated_amount > 0)
                            <a href="{{ route('admin.deposits.refund-form', $deposit) }}" class="erp-btn-secondary text-xs" data-turbo-frame="{{ $turboFrame }}">{{ __('Refund') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-sm text-slate-500">{{ __('No customer deposits found.') }}</td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $deposits->links() }}</div>
</x-admin-layout>
