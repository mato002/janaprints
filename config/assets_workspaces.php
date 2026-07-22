<?php

/**
 * Assets workspace hub catalog (presentation only).
 */
return [

    'groups' => [
        [
            'label' => 'Asset Management',
            'items' => [
                [
                    'key' => 'asset-management',
                    'label' => 'Asset Management',
                    'description' => 'Register, categories, and KPIs in one workspace.',
                    'route' => 'admin.assets.index',
                    'permission' => 'assets.view',
                    'icon' => 'chip',
                    'count_key' => 'register',
                    'active_routes' => [
                        'admin.assets.index',
                        'admin.assets.dashboard',
                        'admin.assets.categories.index',
                        'admin.assets.categories.*',
                        'admin.assets.show',
                        'admin.assets.create',
                        'admin.assets.edit',
                        'admin.assets.store',
                        'admin.assets.update',
                    ],
                    'toolbar_actions' => [
                        [
                            'label' => 'Register asset',
                            'route' => 'admin.assets.create',
                            'permission' => 'assets.create',
                            'modal' => true,
                        ],
                        [
                            'label' => 'New category',
                            'route' => 'admin.assets.categories.create',
                            'permission' => 'assets.categories.manage',
                            'modal' => true,
                        ],
                    ],
                ],
                [
                    'label' => 'Machines',
                    'description' => 'Production machines — availability, utilization, and assignments.',
                    'route' => 'admin.assets.machines.index',
                    'permission' => 'machines.view',
                    'icon' => 'cog',
                    'count_key' => 'machines',
                    'active_routes' => ['admin.assets.machines.index', 'admin.assets.machines.dashboard', 'admin.assets.machines.create', 'admin.assets.machines.show'],
                ],
            ],
        ],
        [
            'label' => 'Maintenance',
            'items' => [
                [
                    'label' => 'Maintenance',
                    'description' => 'Work orders, plans, calendar, downtime, and technicians.',
                    'route' => 'admin.assets.maintenance.dashboard',
                    'permission' => 'maintenance.view',
                    'icon' => 'cog',
                    'count_key' => 'maintenance_open',
                    'active_routes' => ['admin.assets.maintenance.*'],
                ],
            ],
        ],
        [
            'label' => 'Custody',
            'items' => [
                [
                    'label' => 'Custody',
                    'description' => 'Assignments, handovers, returns, and branch transfers.',
                    'route' => 'admin.assets.custody.dashboard',
                    'permission' => 'assets.custody.view',
                    'icon' => 'user',
                    'count_key' => 'custody_pending',
                    'active_routes' => ['admin.assets.custody.*'],
                ],
            ],
        ],
        [
            'label' => 'Finance',
            'items' => [
                [
                    'label' => 'Finance',
                    'description' => 'Depreciation, reconciliation, reports, and write-offs.',
                    'route' => 'admin.assets.finance.dashboard',
                    'permission' => 'assets.depreciation.view',
                    'icon' => 'chart-pie',
                    'active_routes' => ['admin.assets.finance.*'],
                ],
            ],
        ],
        [
            'label' => 'Acquisitions',
            'items' => [
                [
                    'key' => 'acquisitions',
                    'label' => 'Acquisitions',
                    'description' => 'Capitalization queue, warranties, and procurement alignment.',
                    'route' => 'admin.assets.acquisitions.dashboard',
                    'permission' => 'assets.acquisition.view|assets.reconciliation.view',
                    'icon' => 'inbox',
                    'count_key' => 'capitalization_pending',
                    'active_routes' => [
                        'admin.assets.acquisitions.dashboard',
                        'admin.assets.acquisitions.queue',
                        'admin.assets.acquisitions.workbench',
                        'admin.assets.acquisitions.warranties',
                        'admin.assets.acquisitions.warranties.*',
                        'admin.assets.acquisitions.reconciliation.*',
                        'admin.assets.acquisitions.recovery.*',
                    ],
                ],
            ],
        ],
        [
            'label' => 'Intelligence',
            'items' => [
                [
                    'key' => 'intelligence',
                    'label' => 'Intelligence',
                    'description' => 'Executive, branch, and lifecycle analytics.',
                    'route' => 'admin.assets.intelligence.dashboard',
                    'permission' => 'assets.analytics.view',
                    'icon' => 'chart-pie',
                    'active_routes' => [
                        'admin.assets.intelligence.dashboard',
                        'admin.assets.intelligence.executive',
                        'admin.assets.intelligence.branch',
                        'admin.assets.intelligence.analytics',
                        'admin.assets.360.show',
                    ],
                ],
            ],
        ],
    ],

];
