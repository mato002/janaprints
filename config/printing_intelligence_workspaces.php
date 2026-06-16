<?php

/**
 * Printing Intelligence workspace catalog (presentation only).
 * Primary tabs = hub sections; secondary tabs = feature workspaces inside each section.
 */
return [

    'hub' => [
        [
            'label' => 'Overview',
            'description' => 'Unified printing intelligence dashboard and KPIs.',
            'route' => 'admin.workspaces.printing-intelligence.section',
            'route_params' => ['section' => 'overview'],
            'permission' => 'printing.intelligence.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.printing-intelligence.section:overview', 'admin.printing-intelligence.overview', 'admin.workspaces.printing-intelligence'],
        ],
        [
            'label' => 'Analysis',
            'description' => 'Artwork, machine, ink, and material intelligence.',
            'route' => 'admin.workspaces.printing-intelligence.section',
            'route_params' => ['section' => 'analysis'],
            'permission' => 'printing.intelligence.view',
            'icon' => 'photograph',
            'active_routes' => [
                'admin.workspaces.printing-intelligence.section:analysis',
                'admin.printing-intelligence.artwork-analysis.*',
                'admin.printing-intelligence.machines',
                'admin.printing-intelligence.ink',
                'admin.printing-intelligence.inks',
                'admin.printing-intelligence.material',
                'admin.printing-intelligence.materials',
            ],
        ],
        [
            'label' => 'Costing',
            'description' => 'Cost composition, quotations, and estimate vs actual learning.',
            'route' => 'admin.workspaces.printing-intelligence.section',
            'route_params' => ['section' => 'costing'],
            'permission' => 'printing.intelligence.view|printing.estimate-actual.view',
            'icon' => 'currency-dollar',
            'active_routes' => [
                'admin.workspaces.printing-intelligence.section:costing',
                'admin.printing-intelligence.cost',
                'admin.printing-intelligence.cost-intelligence',
                'admin.printing-intelligence.quotations',
                'admin.printing-intelligence.quotation-intelligence',
                'admin.printing-intelligence.estimate-vs-actual*',
            ],
        ],
        [
            'label' => 'Governance',
            'description' => 'Calibration rules, formula governance, and production profitability.',
            'route' => 'admin.workspaces.printing-intelligence.section',
            'route_params' => ['section' => 'governance'],
            'permission' => 'printing.calibration.view|printing.profitability.view',
            'icon' => 'shield-check',
            'active_routes' => [
                'admin.workspaces.printing-intelligence.section:governance',
                'admin.printing-intelligence.calibration*',
                'admin.printing-intelligence.production-profitability*',
            ],
        ],
        [
            'label' => 'Executive',
            'description' => 'Forecasting, scenarios, and operational advisory.',
            'route' => 'admin.workspaces.printing-intelligence.section',
            'route_params' => ['section' => 'executive'],
            'permission' => 'printing.executive.view|printing.advisor.view',
            'icon' => 'presentation-chart-line',
            'active_routes' => [
                'admin.workspaces.printing-intelligence.section:executive',
                'admin.printing-intelligence.executive-intelligence*',
                'admin.printing-intelligence.operations-advisor*',
            ],
        ],
        [
            'label' => 'Settings',
            'description' => 'Printing intelligence thresholds and feature flags.',
            'route' => 'admin.workspaces.printing-intelligence.section',
            'route_params' => ['section' => 'settings'],
            'permission' => 'printing.intelligence.configure',
            'icon' => 'sliders',
            'active_routes' => [
                'admin.workspaces.printing-intelligence.section:settings',
                'admin.printing-intelligence.configuration',
            ],
        ],
    ],

    'sections' => [

        'overview' => [
            'title' => 'Overview',
            'description' => 'Unified operational intelligence across artwork, costing, profitability, and forecasting.',
            'icon' => 'chart-pie',
            'groups' => [[
                'label' => 'Overview',
                'items' => [
                    [
                        'key' => 'dashboard',
                        'label' => 'Overview',
                        'description' => 'Printing intelligence KPIs and quick links.',
                        'route' => 'admin.printing-intelligence.overview',
                        'permission' => 'printing.intelligence.view',
                        'icon' => 'chart-pie',
                        'active_routes' => ['admin.printing-intelligence.overview'],
                    ],
                ],
            ]],
        ],

        'analysis' => [
            'title' => 'Analysis',
            'description' => 'Artwork metadata, machine costing, ink profiles, and material velocity.',
            'icon' => 'photograph',
            'groups' => [[
                'label' => 'Analysis',
                'items' => [
                    [
                        'key' => 'artwork-analysis',
                        'label' => 'Artwork Analysis',
                        'description' => 'Upload artwork and extract file structure metadata (PI1).',
                        'route' => 'admin.printing-intelligence.artwork-analysis.index',
                        'permission' => 'printing.intelligence.view',
                        'icon' => 'photograph',
                        'active_routes' => ['admin.printing-intelligence.artwork-analysis.*'],
                    ],
                    [
                        'key' => 'machine-intelligence',
                        'label' => 'Machine Intelligence',
                        'description' => 'Machine costing, utilization, profitability, and forecasting.',
                        'route' => 'admin.printing-intelligence.machines',
                        'permission' => 'printing.intelligence.view',
                        'icon' => 'cog',
                        'active_routes' => ['admin.printing-intelligence.machines'],
                    ],
                    [
                        'key' => 'ink-intelligence',
                        'label' => 'Ink Intelligence',
                        'description' => 'Ink profiles, costing, coverage, and consumption trends.',
                        'route' => 'admin.printing-intelligence.ink',
                        'permission' => 'printing.intelligence.view',
                        'icon' => 'color-swatch',
                        'active_routes' => ['admin.printing-intelligence.ink', 'admin.printing-intelligence.inks'],
                    ],
                    [
                        'key' => 'material-intelligence',
                        'label' => 'Material Intelligence',
                        'description' => 'Material costs, velocity, dead stock, and risk.',
                        'route' => 'admin.printing-intelligence.material',
                        'permission' => 'printing.intelligence.view',
                        'icon' => 'cube',
                        'active_routes' => ['admin.printing-intelligence.material', 'admin.printing-intelligence.materials'],
                    ],
                ],
            ]],
        ],

        'costing' => [
            'title' => 'Costing',
            'description' => 'Cost composition, quotation estimates, and estimate vs actual learning.',
            'icon' => 'currency-dollar',
            'groups' => [[
                'label' => 'Costing',
                'items' => [
                    [
                        'key' => 'cost-intelligence',
                        'label' => 'Cost Intelligence',
                        'description' => 'Cost composition, accuracy, calibration, and profitability.',
                        'route' => 'admin.printing-intelligence.cost',
                        'permission' => 'printing.intelligence.view',
                        'icon' => 'currency-dollar',
                        'active_routes' => ['admin.printing-intelligence.cost', 'admin.printing-intelligence.cost-intelligence'],
                    ],
                    [
                        'key' => 'quotation-intelligence',
                        'label' => 'Quotation Intelligence',
                        'description' => 'Quotation estimates, accuracy, and profitability.',
                        'route' => 'admin.printing-intelligence.quotations',
                        'permission' => 'printing.intelligence.view',
                        'icon' => 'document-text',
                        'active_routes' => ['admin.printing-intelligence.quotations', 'admin.printing-intelligence.quotation-intelligence'],
                    ],
                    [
                        'key' => 'estimate-vs-actual',
                        'label' => 'Estimate vs Actual',
                        'description' => 'PI6 estimate vs actual learning.',
                        'route' => 'admin.printing-intelligence.estimate-vs-actual',
                        'permission' => 'printing.estimate-actual.view',
                        'icon' => 'scale',
                        'active_routes' => ['admin.printing-intelligence.estimate-vs-actual*'],
                    ],
                ],
            ]],
        ],

        'governance' => [
            'title' => 'Governance',
            'description' => 'Calibration rules, formula governance, and production profitability.',
            'icon' => 'shield-check',
            'groups' => [[
                'label' => 'Governance',
                'items' => [
                    [
                        'key' => 'calibration-governance',
                        'label' => 'Cost Accuracy Governance',
                        'description' => 'PI7 calibration rules and formula governance.',
                        'route' => 'admin.printing-intelligence.calibration-governance',
                        'permission' => 'printing.calibration.view',
                        'icon' => 'shield-check',
                        'active_routes' => ['admin.printing-intelligence.calibration*'],
                    ],
                    [
                        'key' => 'production-profitability',
                        'label' => 'Production Profitability',
                        'description' => 'PI8 job, customer, machine, and product profitability.',
                        'route' => 'admin.printing-intelligence.production-profitability',
                        'permission' => 'printing.profitability.view',
                        'icon' => 'chart-bar',
                        'active_routes' => ['admin.printing-intelligence.production-profitability*'],
                    ],
                ],
            ]],
        ],

        'executive' => [
            'title' => 'Executive',
            'description' => 'Forecasting, scenarios, and operational advisory.',
            'icon' => 'presentation-chart-line',
            'groups' => [[
                'label' => 'Executive',
                'items' => [
                    [
                        'key' => 'executive-intelligence',
                        'label' => 'Executive Intelligence',
                        'description' => 'PI9 forecasting, scenarios, and executive alerts.',
                        'route' => 'admin.printing-intelligence.executive-intelligence',
                        'permission' => 'printing.executive.view',
                        'icon' => 'presentation-chart-line',
                        'active_routes' => ['admin.printing-intelligence.executive-intelligence*'],
                    ],
                    [
                        'key' => 'operations-advisor',
                        'label' => 'Operations Advisor',
                        'description' => 'PI10 read-only advisory recommendations across printing operations.',
                        'route' => 'admin.printing-intelligence.operations-advisor',
                        'permission' => 'printing.advisor.view',
                        'icon' => 'light-bulb',
                        'active_routes' => ['admin.printing-intelligence.operations-advisor*'],
                    ],
                ],
            ]],
        ],

        'settings' => [
            'title' => 'Settings',
            'description' => 'Printing intelligence thresholds and feature flags.',
            'icon' => 'sliders',
            'groups' => [[
                'label' => 'Settings',
                'items' => [
                    [
                        'key' => 'configuration',
                        'label' => 'Configuration',
                        'description' => 'Printing intelligence thresholds and feature flags.',
                        'route' => 'admin.printing-intelligence.configuration',
                        'permission' => 'printing.intelligence.configure',
                        'icon' => 'sliders',
                        'active_routes' => ['admin.printing-intelligence.configuration'],
                    ],
                ],
            ]],
        ],

    ],

];
