<x-admin-layout :title="__('Configuration')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Configuration')],
]">
    <x-admin.page-header
        :title="__('Printing Intelligence Configuration')"
        :description="__('Company-level settings override file defaults. Changes apply immediately for your organization.')"
    />

    @include('admin.printing-intelligence.partials.nav')

@include('admin.printing-intelligence.partials.environment-warnings', ['environment' => $environment ?? []])

    <form method="POST" action="{{ route('admin.printing-intelligence.configuration.update') }}" class="space-y-6">
        @csrf

        @foreach (['pricing' => __('Pricing'), 'costing' => __('Costing'), 'features' => __('Features'), 'operations' => __('Operations')] as $group => $heading)
            <x-admin.card>
                <h3 class="mb-4 text-sm font-semibold text-slate-900">{{ $heading }}</h3>
                <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach (collect($rows)->where('group', $group) as $row)
                        <div>
                            <label for="pi-{{ $row['key'] }}" class="block text-xs font-medium text-slate-500">{{ $row['label'] }}</label>
                            @if ($row['type'] === 'boolean')
                                <select id="pi-{{ $row['key'] }}" name="{{ $row['key'] }}" class="erp-input mt-1 w-full text-sm">
                                    <option value="1" @selected((bool) $row['value'])>{{ __('Enabled') }}</option>
                                    <option value="0" @selected(! (bool) $row['value'])>{{ __('Disabled') }}</option>
                                </select>
                            @else
                                <input
                                    id="pi-{{ $row['key'] }}"
                                    type="number"
                                    step="{{ $row['type'] === 'float' ? '0.01' : '1' }}"
                                    name="{{ $row['key'] }}"
                                    value="{{ $row['value'] }}"
                                    class="erp-input mt-1 w-full text-sm"
                                />
                            @endif
                            <p class="mt-1 text-xs text-slate-400">{{ __('Default') }}: {{ is_bool($row['default']) ? ($row['default'] ? __('Enabled') : __('Disabled')) : $row['default'] }}</p>
                        </div>
                    @endforeach
                </dl>
            </x-admin.card>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="erp-btn-primary">{{ __('Save settings') }}</button>
        </div>
    </form>
</x-admin-layout>
