<x-admin-layout :title="__('Supplier statement')">
    <x-admin.page-header :title="__('Supplier statement')" />
    <form method="GET" class="mb-4 flex flex-wrap gap-3 items-end">
        <select name="vendor_id" class="erp-input" required>
            @foreach ($vendors as $v)<option value="{{ $v->id }}">{{ $v->vendor_name }}</option>@endforeach
        </select>
        <input type="date" name="from_date" class="erp-input" required>
        <input type="date" name="to_date" class="erp-input" required>
        <button class="erp-btn-primary">{{ __('Run') }}</button>
    </form>
    @if ($report)
        <x-admin.card>
            <p class="font-medium mb-2">{{ $report['vendor']->vendor_name }}</p>
            <p class="text-sm">{{ __('Opening') }}: {{ number_format($report['opening_balance'], 2) }} · {{ __('Closing') }}: {{ number_format($report['closing_balance'], 2) }}</p>
        </x-admin.card>
    @endif
</x-admin-layout>
