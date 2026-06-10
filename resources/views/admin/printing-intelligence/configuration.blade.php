<x-admin-layout :title="__('Configuration')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Configuration')],
]">
    <x-admin.page-header :title="__('Printing Intelligence Configuration')" :description="__('Read-only view of printing intelligence settings (config/printing_intelligence.php).')" />
    @include('admin.printing-intelligence.partials.nav')

    <x-admin.card>
        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
            @foreach ([
                __('Default margin %') => $config['default_margin_percent'] ?? '—',
                __('Electricity rate (per kWh)') => $config['electricity_rate_per_kwh'] ?? '—',
                __('Labour hourly rate') => $config['labour_hourly_rate'] ?? '—',
                __('Default estimation confidence') => $config['default_estimation_confidence'] ?? '—',
                __('Supported print methods') => implode(', ', $config['supported_print_methods'] ?? []),
                __('Supported ink types') => implode(', ', $config['supported_ink_types'] ?? []),
                __('Future artwork analysis') => ($config['future_artwork_analysis_enabled'] ?? false) ? __('Enabled') : __('Disabled'),
                __('Future AI analysis') => ($config['future_ai_analysis_enabled'] ?? false) ? __('Enabled') : __('Disabled'),
                __('Future estimate learning') => ($config['future_estimate_learning_enabled'] ?? false) ? __('Enabled') : __('Disabled'),
            ] as $label => $value)
                <div>
                    <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-admin.card>
</x-admin-layout>
