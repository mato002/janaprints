<?php

/**
 * Production workspace hub and section catalogs (presentation only).
 */
return [

    'hub' => [
        [
            'label' => 'Operations',
            'description' => 'Job cards, queue, workload, and production pipeline.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'operations'],
            'permission' => 'production.view',
            'icon' => 'collection',
            'active_routes' => ['admin.workspaces.production.section:operations', 'admin.production.dashboard', 'admin.production.job-cards.*', 'admin.production.queue.*'],
        ],
        [
            'label' => 'Planning',
            'description' => 'Machine schedule, assignments, and capacity planning.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'planning'],
            'permission' => 'production.scheduling.view|production.work-centers.view',
            'icon' => 'calendar',
            'active_routes' => ['admin.workspaces.production.section:planning', 'admin.production.scheduling.*', 'admin.production.work-centers.*'],
        ],
        [
            'label' => 'Quality',
            'description' => 'QC queue, checks, rejected jobs, and rework.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'quality'],
            'permission' => 'production.quality.view',
            'icon' => 'badge-check',
            'active_routes' => ['admin.workspaces.production.section:quality', 'admin.production.quality.*'],
        ],
        [
            'label' => 'Dispatch',
            'description' => 'Ready for dispatch, delivery notes, and exceptions.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'dispatch'],
            'permission' => 'dispatch.view',
            'icon' => 'truck',
            'active_routes' => ['admin.workspaces.production.section:dispatch', 'admin.workspaces.dispatch', 'admin.dispatch.*'],
        ],
        [
            'label' => 'Reports',
            'description' => 'Production dashboard, throughput, and utilization.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'reports'],
            'permission' => 'reports.view|production.costing.view|intelligence.production.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.production.section:reports', 'admin.reports.production', 'admin.reports.production360', 'admin.production.costing.*'],
        ],
    ],

    'sections' => [
        'operations' => [
            'title' => 'Operations',
            'description' => 'Job execution, queue, and production pipeline.',
            'icon' => 'collection',
            'groups' => [[
                'label' => 'Operations',
                'items' => [
                    ['key' => 'command-center', 'label' => 'Production Command Center', 'description' => 'Live production intelligence and operational monitoring.', 'route' => 'admin.production.dashboard', 'permission' => 'production.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.production.dashboard']],
                    ['key' => 'job-cards', 'label' => 'Job Cards', 'description' => 'Production order execution register.', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'icon' => 'collection', 'active_routes' => ['admin.production.job-cards.*']],
                    ['key' => 'job-queue', 'label' => 'Production Queue', 'description' => 'Department queue and work assignment register.', 'route' => 'admin.production.queue.index', 'permission' => 'production.queue.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.production.queue.*']],
                ],
            ]],
        ],
        'planning' => [
            'title' => 'Planning',
            'description' => 'Scheduling, work centers, and capacity.',
            'icon' => 'calendar',
            'groups' => [[
                'label' => 'Planning',
                'items' => [
                    ['key' => 'scheduling', 'label' => 'Scheduling', 'description' => 'Production planning and timeline management.', 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view', 'icon' => 'calendar', 'active_routes' => ['admin.production.scheduling.*']],
                    ['key' => 'work-centers', 'label' => 'Work Centers', 'description' => 'Machines, departments and capacity management.', 'route' => 'admin.production.work-centers.index', 'permission' => 'production.work-centers.view', 'icon' => 'chip', 'active_routes' => ['admin.production.work-centers.*']],
                ],
            ]],
        ],
        'quality' => [
            'title' => 'Quality',
            'description' => 'Inspection and approval management.',
            'icon' => 'badge-check',
            'groups' => [[
                'label' => 'Quality',
                'items' => [
                    ['key' => 'quality-control', 'label' => 'Quality Control', 'description' => 'Inspection and approval management.', 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view', 'icon' => 'badge-check', 'active_routes' => ['admin.production.quality.*']],
                ],
            ]],
        ],
        'dispatch' => [
            'title' => 'Dispatch',
            'description' => 'Delivery planning and fulfillment.',
            'icon' => 'truck',
            'groups' => [[
                'label' => 'Dispatch',
                'items' => [
                    ['key' => 'dispatch-workspace', 'label' => 'Dispatch Workspace', 'description' => 'Delivery planning and fulfillment operations.', 'route' => 'admin.workspaces.dispatch', 'permission' => 'dispatch.view', 'icon' => 'truck', 'active_routes' => ['admin.workspaces.dispatch', 'admin.dispatch.*']],
                ],
            ]],
        ],
        'reports' => [
            'title' => 'Reports',
            'description' => 'Production analytics and costing.',
            'icon' => 'chart-pie',
            'groups' => [[
                'label' => 'Reports',
                'items' => [
                    ['key' => 'production-reports', 'label' => 'Production Reports', 'description' => 'Historical performance reporting.', 'route' => 'admin.reports.production', 'permission' => 'reports.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.reports.production', 'admin.reports.production.print']],
                    ['key' => 'production-360', 'label' => 'Production 360', 'description' => 'Strategic production intelligence.', 'route' => 'admin.reports.production360', 'permission' => 'intelligence.production.view|reports.view', 'icon' => 'sparkles', 'active_routes' => ['admin.reports.production360']],
                    ['key' => 'job-costing', 'label' => 'Job Costing & Profitability', 'description' => 'Job-level profitability and cost analysis.', 'route' => 'admin.production.costing.dashboard', 'permission' => 'production.costing.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.production.costing.*', 'admin.production.job-cards.costing']],
                ],
            ]],
        ],
    ],
];
