<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ([
        ['label' => __('PAYE'), 'value' => $statutories['paye']],
        ['label' => __('SHIF'), 'value' => $statutories['shif']],
        ['label' => __('NSSF'), 'value' => $statutories['nssf']],
        ['label' => __('Housing levy'), 'value' => $statutories['housing_levy']],
        ['label' => __('Total statutory'), 'value' => $statutories['total']],
    ] as $item)
        <x-admin.kpi-widget :label="$item['label']" :value="number_format($item['value'], 2)" />
    @endforeach
</div>

@if ($run->payslips->isEmpty())
    <x-admin.card class="mt-4">
        <x-admin.empty-state
            :title="__('No statutory totals yet')"
            :description="__('Kenya statutory deductions are calculated when payroll is generated.')"
        />
    </x-admin.card>
@endif
