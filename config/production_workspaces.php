<?php

/**
 * Production workspace hub catalog (presentation only).
 * Hub is navigation only — features live on dedicated module pages.
 */
return [

    'groups' => [
        [
            'label' => 'Operations',
            'items' => [
                [
                    'label' => 'Production Command Center',
                    'description' => 'Live production intelligence and operational monitoring.',
                    'route' => 'admin.production.dashboard',
                    'permission' => 'production.view',
                    'icon' => 'chart-pie',
                    'count_key' => 'open_jobs',
                    'active_routes' => ['admin.production.dashboard'],
                ],
                [
                    'label' => 'Job Cards',
                    'description' => 'Production order execution register.',
                    'route' => 'admin.production.job-cards.index',
                    'permission' => 'production.view',
                    'icon' => 'collection',
                    'count_key' => 'job_cards',
                    'active_routes' => ['admin.production.job-cards.*'],
                ],
                [
                    'label' => 'Production Queue',
                    'description' => 'Department queue and work assignment register.',
                    'route' => 'admin.production.queue.index',
                    'permission' => 'production.queue.view',
                    'icon' => 'switch-horizontal',
                    'count_key' => 'active_queue',
                    'active_routes' => ['admin.production.queue.*'],
                ],
                [
                    'label' => 'Scheduling',
                    'description' => 'Production planning and timeline management.',
                    'route' => 'admin.production.scheduling.index',
                    'permission' => 'production.scheduling.view',
                    'icon' => 'calendar',
                    'count_key' => 'scheduled_jobs',
                    'active_routes' => ['admin.production.scheduling.*'],
                ],
                [
                    'label' => 'Quality Control',
                    'description' => 'Inspection and approval management.',
                    'route' => 'admin.production.quality.index',
                    'permission' => 'production.quality.view',
                    'icon' => 'badge-check',
                    'count_key' => 'pending_qc',
                    'active_routes' => ['admin.production.quality.*'],
                ],
                [
                    'label' => 'Work Centers',
                    'description' => 'Machines, departments and capacity management.',
                    'route' => 'admin.production.work-centers.index',
                    'permission' => 'production.work-centers.view',
                    'icon' => 'chip',
                    'count_key' => 'work_centers',
                    'active_routes' => ['admin.production.work-centers.*'],
                ],
            ],
        ],
        [
            'label' => 'Financial',
            'items' => [
                [
                    'label' => 'Job Costing & Profitability',
                    'description' => 'Job-level profitability and cost analysis.',
                    'route' => 'admin.production.costing.dashboard',
                    'permission' => 'production.costing.view',
                    'icon' => 'currency-dollar',
                    'count_key' => 'costed_jobs',
                    'active_routes' => ['admin.production.costing.*', 'admin.production.job-cards.costing'],
                ],
            ],
        ],
        [
            'label' => 'Logistics',
            'items' => [
                [
                    'label' => 'Dispatch Workspace',
                    'description' => 'Delivery planning and fulfillment operations.',
                    'route' => 'admin.workspaces.dispatch',
                    'permission' => 'dispatch.view',
                    'icon' => 'truck',
                    'count_key' => 'dispatch_ready',
                    'active_routes' => ['admin.workspaces.dispatch', 'admin.dispatch.*'],
                ],
            ],
        ],
        [
            'label' => 'Intelligence',
            'items' => [
                [
                    'label' => 'Production 360',
                    'description' => 'Strategic production intelligence.',
                    'route' => 'admin.reports.production360',
                    'permission' => 'intelligence.production.view|reports.view',
                    'icon' => 'sparkles',
                    'count_key' => 'active_jobs',
                    'active_routes' => ['admin.reports.production360'],
                ],
            ],
        ],
        [
            'label' => 'Reporting',
            'items' => [
                [
                    'label' => 'Production Reports',
                    'description' => 'Historical performance reporting.',
                    'route' => 'admin.reports.production',
                    'permission' => 'reports.view',
                    'icon' => 'chart-bar',
                    'count_key' => 'completed_period',
                    'active_routes' => ['admin.reports.production', 'admin.reports.production.print'],
                ],
            ],
        ],
    ],

];
