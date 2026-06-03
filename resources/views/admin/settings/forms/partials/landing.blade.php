@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);

    $formIcons = [
        'customer' => 'users',
        'lead' => 'sparkles',
        'quotation' => 'document-text',
        'artwork' => 'color-swatch',
        'sales_order' => 'shopping-cart',
        'inventory_item' => 'cube',
    ];

    $extraCards = [
        [
            'title' => __('Production'),
            'description' => __('Job card and shop floor form fields.'),
            'icon' => 'chip',
            'comingSoon' => true,
            'status' => __('Planned'),
            'statusVariant' => 'warning',
        ],
        [
            'title' => __('Procurement'),
            'description' => __('Purchase orders and supplier forms.'),
            'icon' => 'truck',
            'comingSoon' => true,
            'status' => __('Planned'),
            'statusVariant' => 'warning',
        ],
        [
            'title' => __('Finance'),
            'description' => __('Invoice and payment capture fields.'),
            'icon' => 'currency-dollar',
            'comingSoon' => true,
            'status' => __('Planned'),
            'statusVariant' => 'warning',
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($forms as $form)
        @include('admin.settings.partials.control-center-card', [
            'title' => $form['label'],
            'description' => $form['description'],
            'icon' => $formIcons[$form['form_key']] ?? 'clipboard-list',
            'href' => route('admin.settings.forms.index', ['form' => $form['form_key']] + $scopeQuery),
            'status' => count($form['fields']) . ' ' . __('fields'),
            'statusVariant' => $form['is_active'] ? 'success' : 'warning',
        ])
    @endforeach

    @foreach ($extraCards as $card)
        @include('admin.settings.partials.control-center-card', $card)
    @endforeach
</div>
