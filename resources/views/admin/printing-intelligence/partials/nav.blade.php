@php
    $nav = [
        ['label' => __('Overview'), 'route' => 'admin.printing-intelligence.overview', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Artwork Analysis'), 'route' => 'admin.printing-intelligence.artwork-analysis.index', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Machine Intelligence'), 'route' => 'admin.printing-intelligence.machines', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Ink Intelligence'), 'route' => 'admin.printing-intelligence.ink', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Material Intelligence'), 'route' => 'admin.printing-intelligence.material', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Cost Intelligence'), 'route' => 'admin.printing-intelligence.cost', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Quotation Intelligence'), 'route' => 'admin.printing-intelligence.quotations', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Estimate vs Actual'), 'route' => 'admin.printing-intelligence.estimate-vs-actual', 'permission' => 'printing.estimate-actual.view'],
        ['label' => __('Cost Accuracy Governance'), 'route' => 'admin.printing-intelligence.calibration-governance', 'permission' => 'printing.calibration.view'],
        ['label' => __('Production Profitability'), 'route' => 'admin.printing-intelligence.production-profitability', 'permission' => 'printing.profitability.view'],
        ['label' => __('Executive Intelligence'), 'route' => 'admin.printing-intelligence.executive-intelligence', 'permission' => 'printing.executive.view'],
        ['label' => __('Operations Advisor'), 'route' => 'admin.printing-intelligence.operations-advisor', 'permission' => 'printing.advisor.view'],
        ['label' => __('Configuration'), 'route' => 'admin.printing-intelligence.configuration', 'permission' => 'printing.intelligence.configure'],
    ];
@endphp

<x-admin.workspace-nav :links="$nav" variant="compact" class="mb-4 rounded-lg border border-slate-200 bg-white p-3" />
