<?php

/**
 * Printing Intelligence workspace catalog (presentation only).
 */
return [

    'hub' => [
        [
            'label' => 'Overview',
            'description' => 'Unified printing intelligence dashboard and quick links.',
            'route' => 'admin.printing-intelligence.overview',
            'permission' => 'printing.intelligence.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.printing-intelligence.overview', 'admin.workspaces.printing-intelligence'],
        ],
        [
            'label' => 'Artwork Analysis',
            'description' => 'Upload artwork and extract file structure metadata (PI1).',
            'route' => 'admin.printing-intelligence.artwork-analysis.index',
            'permission' => 'printing.intelligence.view',
            'icon' => 'photograph',
            'active_routes' => ['admin.printing-intelligence.artwork-analysis.*'],
        ],
        [
            'label' => 'Machine Intelligence',
            'description' => 'Machine costing, utilization, profitability, and forecasting.',
            'route' => 'admin.printing-intelligence.machines',
            'permission' => 'printing.intelligence.view',
            'icon' => 'cog',
            'active_routes' => ['admin.printing-intelligence.machines'],
        ],
        [
            'label' => 'Ink Intelligence',
            'description' => 'Ink profiles, costing, coverage, and consumption trends.',
            'route' => 'admin.printing-intelligence.ink',
            'permission' => 'printing.intelligence.view',
            'icon' => 'color-swatch',
            'active_routes' => ['admin.printing-intelligence.ink', 'admin.printing-intelligence.inks'],
        ],
        [
            'label' => 'Material Intelligence',
            'description' => 'Material costs, velocity, dead stock, and risk.',
            'route' => 'admin.printing-intelligence.material',
            'permission' => 'printing.intelligence.view',
            'icon' => 'cube',
            'active_routes' => ['admin.printing-intelligence.material', 'admin.printing-intelligence.materials'],
        ],
        [
            'label' => 'Cost Intelligence',
            'description' => 'Cost composition, accuracy, calibration, and profitability.',
            'route' => 'admin.printing-intelligence.cost',
            'permission' => 'printing.intelligence.view',
            'icon' => 'currency-dollar',
            'active_routes' => ['admin.printing-intelligence.cost', 'admin.printing-intelligence.cost-intelligence'],
        ],
        [
            'label' => 'Quotation Intelligence',
            'description' => 'Quotation estimates, accuracy, and profitability.',
            'route' => 'admin.printing-intelligence.quotations',
            'permission' => 'printing.intelligence.view',
            'icon' => 'document-text',
            'active_routes' => ['admin.printing-intelligence.quotations', 'admin.printing-intelligence.quotation-intelligence'],
        ],
        [
            'label' => 'Estimate vs Actual',
            'description' => 'PI6 estimate vs actual learning.',
            'route' => 'admin.printing-intelligence.estimate-vs-actual',
            'permission' => 'printing.estimate-actual.view',
            'icon' => 'scale',
            'active_routes' => ['admin.printing-intelligence.estimate-vs-actual*'],
        ],
        [
            'label' => 'Cost Accuracy Governance',
            'description' => 'PI7 calibration rules and formula governance.',
            'route' => 'admin.printing-intelligence.calibration-governance',
            'permission' => 'printing.calibration.view',
            'icon' => 'shield-check',
            'active_routes' => ['admin.printing-intelligence.calibration*'],
        ],
        [
            'label' => 'Production Profitability',
            'description' => 'PI8 job, customer, machine, and product profitability.',
            'route' => 'admin.printing-intelligence.production-profitability',
            'permission' => 'printing.profitability.view',
            'icon' => 'chart-bar',
            'active_routes' => ['admin.printing-intelligence.production-profitability*'],
        ],
        [
            'label' => 'Executive Intelligence',
            'description' => 'PI9 forecasting, scenarios, and executive alerts.',
            'route' => 'admin.printing-intelligence.executive-intelligence',
            'permission' => 'printing.executive.view',
            'icon' => 'presentation-chart-line',
            'active_routes' => ['admin.printing-intelligence.executive-intelligence*'],
        ],
        [
            'label' => 'Operations Advisor',
            'description' => 'PI10 read-only advisory recommendations across printing operations.',
            'route' => 'admin.printing-intelligence.operations-advisor',
            'permission' => 'printing.advisor.view',
            'icon' => 'light-bulb',
            'active_routes' => ['admin.printing-intelligence.operations-advisor*'],
        ],
        [
            'label' => 'Configuration',
            'description' => 'Printing intelligence thresholds and feature flags.',
            'route' => 'admin.printing-intelligence.configuration',
            'permission' => 'printing.intelligence.configure',
            'icon' => 'sliders',
            'active_routes' => ['admin.printing-intelligence.configuration'],
        ],
    ],
];
