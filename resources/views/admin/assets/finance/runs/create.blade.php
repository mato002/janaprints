<x-admin-layout :title="__('New Depreciation Run')" :breadcrumbs="[['label' => __('Finance'), 'url' => route('admin.assets.finance.dashboard', ['tab' => 'runs'])], ['label' => __('New')]]">
    <x-admin.page-header :title="__('Create Depreciation Run')" />

    <x-admin.alert variant="warning" class="mb-4">
        {{ __('Only straight-line depreciation is fully supported for posting. Reducing balance and units-of-production methods are blocked at preview and execution.') }}
    </x-admin.alert>

    <x-admin.card>
        <form method="POST" action="{{ route('admin.assets.finance.runs.store') }}" class="max-w-md space-y-4">
            @csrf
            <div>
                <label class="erp-label">{{ __('Period (YYYY-MM)') }}</label>
                <input type="month" name="period" value="{{ old('period', $defaultPeriod) }}" class="erp-input w-full" required>
            </div>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_dry_run" value="1">
                {{ __('Dry run (preview only)') }}
            </label>
            <button type="submit" class="erp-btn-primary">{{ __('Create Run') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
