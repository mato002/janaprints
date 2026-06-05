@props(['tab_data', 'has_production_pipeline', 'has_dispatch_pipeline'])

@if (($tab_data['type'] ?? '') === 'placeholder')
    <x-admin.card>
        <x-admin.empty-state icon="sparkles" :title="__('Conversion Reports')" :description="$tab_data['message'] ?? __('No data available.')" />
    </x-admin.card>
@elseif (($tab_data['type'] ?? '') === 'funnel')
    @if (! $has_production_pipeline || ! $has_dispatch_pipeline)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            @if (! $has_production_pipeline)
                <p>{{ __('Production pipeline data is unavailable — production stage counts will show zero.') }}</p>
            @endif
            @if (! $has_dispatch_pipeline)
                <p>{{ __('Dispatch pipeline data is unavailable — dispatch and delivery stage counts will show zero.') }}</p>
            @endif
        </div>
    @endif

    @include('admin.commercial.reports.conversion.partials.funnel-cards', [
        'stages' => $tab_data['stages'] ?? [],
        'focus_label' => $tab_data['focus_label'] ?? __('Commercial Funnel'),
    ])

    <div class="grid gap-6 lg:grid-cols-2">
        @include('admin.commercial.reports.conversion.partials.simple-table', [
            'title' => __('Stage Drop-off'),
            'columns' => [__('Stage'), __('Count'), __('Conversion'), __('Drop-off')],
            'rows' => collect($tab_data['drop_off'] ?? [])->map(fn ($row) => array_values($row))->all(),
        ])

        @include('admin.commercial.reports.conversion.partials.simple-table', [
            'title' => __('Branch Conversion'),
            'columns' => [__('Branch'), __('Leads'), __('Quotes'), __('Orders'), __('Lead→Quote'), __('Quote→Order')],
            'rows' => collect($tab_data['branch_rows'] ?? [])->map(fn ($row) => [
                $row['branch'],
                $row['leads'],
                $row['quotes'],
                $row['orders'],
                $row['lead_to_quote'],
                $row['quote_to_order'],
            ])->all(),
        ])
    </div>

    <x-admin.card class="mt-6">
        @include('admin.commercial.reports.conversion.partials.simple-table', [
            'title' => __('Salesperson Conversion'),
            'columns' => [__('Salesperson'), __('Leads'), __('Quotes'), __('Orders'), __('Lead→Quote'), __('Quote→Order')],
            'rows' => collect($tab_data['salesperson_rows'] ?? [])->map(fn ($row) => [
                $row['salesperson'],
                $row['leads'],
                $row['quotes'],
                $row['orders'],
                $row['lead_to_quote'],
                $row['quote_to_order'],
            ])->all(),
        ])
    </x-admin.card>
@endif
