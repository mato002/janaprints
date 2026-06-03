@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);

    $ruleIcons = [
        'quotation_approval' => 'document-text',
        'discount_approval' => 'tag',
        'stock_adjustment_approval' => 'cube',
        'procurement_approval' => 'truck',
        'payment_approval' => 'currency-dollar',
    ];
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach ($rows as $row)
        @php
            $tierCount = count($row['tiers'] !== [] ? $row['tiers'] : ($row['company_tiers'] ?? []));
            $status = $row['is_enabled']
                ? ($tierCount > 0 ? $tierCount . ' ' . __('tiers') : __('Active'))
                : __('Inactive');
            $statusVariant = $row['is_enabled'] ? 'success' : 'warning';
        @endphp
        @include('admin.settings.partials.control-center-card', [
            'title' => $row['label'],
            'description' => $row['description'],
            'icon' => $ruleIcons[$row['rule_type']] ?? 'shield-check',
            'href' => route('admin.settings.approvals.index', ['rule' => $row['rule_type']] + $scopeQuery),
            'status' => $status,
            'statusVariant' => $statusVariant,
        ])
    @endforeach
</div>
