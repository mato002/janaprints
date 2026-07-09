@php
    $wallet = $wallet ?? app(\App\Support\Sales\CustomerCreditWalletService::class)->summary($customerId);
@endphp

<div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
    <x-admin.kpi-widget :label="__('Available credit')" :value="number_format($wallet['available_credit'], 2)" icon="currency-dollar" />
    <x-admin.kpi-widget :label="__('Used credit')" :value="number_format($wallet['used_credit'], 2)" icon="scale" />
    <x-admin.kpi-widget :label="__('Remaining credit')" :value="number_format($wallet['remaining_credit'], 2)" icon="cash" />
</div>

@if (! empty($wallet['deposits']))
    <x-admin.card class="mb-4">
        <h3 class="mb-3 font-medium">{{ __('Deposit credits') }}</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase text-slate-400">
                    <th>{{ __('Payment') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Issued') }}</th>
                    <th>{{ __('Applied') }}</th>
                    <th>{{ __('Refunded') }}</th>
                    <th>{{ __('Remaining') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($wallet['deposits'] as $row)
                    <tr class="border-t border-erp-border">
                        <td class="py-2">
                            <a href="{{ route('admin.payments.show', $row['payment_public_id'] ?? $row['payment_id']) }}" class="font-mono text-erp-accent">{{ $row['payment_number'] }}</a>
                        </td>
                        <td class="py-2">{{ $row['payment_date'] }}</td>
                        <td class="py-2 font-mono">{{ number_format($row['credit_issued'], 2) }}</td>
                        <td class="py-2 font-mono">{{ number_format($row['credit_applied'], 2) }}</td>
                        <td class="py-2 font-mono">{{ number_format($row['credit_refunded'], 2) }}</td>
                        <td class="py-2 font-mono">{{ number_format($row['credit_remaining'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
@endif
