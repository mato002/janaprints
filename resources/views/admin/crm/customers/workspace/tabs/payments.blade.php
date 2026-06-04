@php
    $payments = $tabData['payments'] ?? null;
@endphp

@if (! empty($tabData['restricted']))
    <x-admin.empty-state icon="lock-closed" :title="__('Access restricted')" />
@else
    <x-admin.data-table :search-placeholder="__('Search payments…')" export-filename="customer-payments">
        <x-slot name="head">
            <tr>
                <th>{{ __('Payment') }}</th>
                <th>{{ __('Date') }}</th>
                <th class="text-right">{{ __('Amount') }}</th>
                <th class="text-right">{{ __('Allocated') }}</th>
                <th class="text-right">{{ __('Unallocated') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($payments as $payment)
                @php
                    $allocated = (float) ($payment->allocated_sum ?? $payment->amount - $payment->unallocated_amount);
                @endphp
                <tr x-show="rowVisible(@js(strtolower($payment->payment_number)))">
                    <td>
                        <a href="{{ route('admin.accounting.payments.show', $payment) }}" class="font-mono text-indigo-600 hover:text-indigo-800">
                            {{ $payment->payment_number }}
                        </a>
                    </td>
                    <td>{{ $payment->payment_date->format('M j, Y') }}</td>
                    <td class="text-right font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($allocated, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($payment->unallocated_amount, 2) }}</td>
                    <td><x-admin.enum-status-badge :status="$payment->status->value" /></td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="credit-card" :title="__('No payments recorded')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">
            @if ($payments && method_exists($payments, 'links'))
                <x-admin.table-pagination :paginator="$payments" />
            @endif
        </x-slot>
    </x-admin.data-table>
@endif
