<x-admin-layout :title="__('Exchange Rates')" :breadcrumbs="[['label' => __('Currencies'), 'url' => route('admin.accounting.currencies.index')], ['label' => __('Exchange Rates')]]">
    <x-admin.page-header :title="__('Exchange Rates')" :description="__('Rates to base currency :code', ['code' => $baseCurrency])" />

    @can('accounting.currencies.manage')
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-3">{{ __('Add / update rate') }}</h3>
            <form method="POST" action="{{ route('admin.accounting.currencies.rates.store') }}" class="grid gap-3 sm:grid-cols-4 max-w-4xl">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Currency') }}</label>
                    <select name="currency_code" class="erp-input" required>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->code }}">{{ $currency->code }} — {{ $currency->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Rate date') }}</label>
                    <input type="date" name="rate_date" value="{{ now()->toDateString() }}" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Rate to :code', ['code' => $baseCurrency]) }}</label>
                    <input type="number" step="0.00000001" name="rate_to_base" class="erp-input" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Source') }}</label>
                    <input type="text" name="source" class="erp-input" placeholder="{{ __('Manual') }}">
                </div>
                <div class="sm:col-span-4">
                    <button type="submit" class="erp-btn-primary">{{ __('Save rate') }}</button>
                </div>
            </form>
        </x-admin.card>
    @endcan

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Date') }}</th>
                    <th class="py-2">{{ __('Currency') }}</th>
                    <th class="py-2 text-right">{{ __('Rate to base') }}</th>
                    <th class="py-2">{{ __('Source') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rates as $rate)
                    <tr class="border-t border-erp-border">
                        <td class="py-2">{{ $rate->rate_date->format('Y-m-d') }}</td>
                        <td class="py-2 font-mono">{{ $rate->currency_code }}</td>
                        <td class="py-2 text-right font-mono">{{ number_format((float) $rate->rate_to_base, 8) }}</td>
                        <td class="py-2">{{ $rate->source ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-slate-500">{{ __('No exchange rates recorded.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $rates->links() }}</div>
    </x-admin.card>
</x-admin-layout>
