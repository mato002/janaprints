<?php

/**
 * Production workspace hub and section catalogs (presentation only).
 */
return [

    'hub' => [
        [
            'label' => 'Operations',
            'description' => 'Production floor, job execution, and pipeline.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'operations'],
            'permission' => 'production.view',
            'icon' => 'collection',
            'active_routes' => ['admin.workspaces.production.section:operations', 'admin.production.floor', 'admin.production.home', 'admin.production.job-cards.*', 'admin.production.queue.*', 'admin.production.outputs.*'],
        ],
        [
            'label' => 'Planning',
            'description' => 'Machine schedule, assignments, and capacity planning.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'planning'],
            'permission' => 'production.scheduling.view|production.work-centers.view',
            'icon' => 'calendar',
            'active_routes' => ['admin.workspaces.production.section:planning', 'admin.production.scheduling.*', 'admin.production.work-centers.*', 'admin.production.boms.*', 'admin.production.print-templates.*'],
        ],
        [
            'label' => 'Quality',
            'description' => 'QC inspections and rework follow-up.',
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
            'active_routes' => ['admin.workspaces.production.section:dispatch', 'admin.dispatch.*'],
        ],
        [
            'label' => 'Reports',
            'description' => 'Production dashboard, throughput, and utilization.',
            'route' => 'admin.workspaces.production.section',
            'route_params' => ['section' => 'reports'],
            'permission' => 'reports.view|production.costing.view|intelligence.production.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.production.section:reports', 'admin.reports.production', 'admin.reports.production360', 'admin.production.costing.*', 'admin.production.dashboard'],
        ],
    ],

    'sections' => [
        'operations' => [
            'title' => 'Operations',
            'description' => 'Run the shop floor from one register.',
            'icon' => 'collection',
            'groups' => [[
                'label' => 'Operations',
                'items' => [
                    ['key' => 'production-floor', 'label' => 'Production Floor', 'description' => 'Table-first shop floor register with next-step actions and vendor tracking.', 'route' => 'admin.production.floor', 'permission' => 'production.view', 'icon' => 'view-grid', 'count_key' => 'open_jobs', 'active_routes' => ['admin.production.floor', 'admin.production.home']],
                    ['key' => 'job-cards', 'label' => 'All Job Cards', 'description' => 'Full job card register with advanced filters and exports.', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'icon' => 'collection', 'count_key' => 'job_cards', 'active_routes' => ['admin.production.job-cards.*']],
                    ['key' => 'job-queue', 'label' => 'Work Center Queue', 'description' => 'Department queue detail when you need per-center assignment.', 'route' => 'admin.production.queue.index', 'permission' => 'production.queue.view', 'icon' => 'switch-horizontal', 'count_key' => 'active_queue', 'active_routes' => ['admin.production.queue.*']],
                    ['key' => 'outputs', 'label' => 'Finished Goods Outputs', 'description' => 'Register of posted production outputs.', 'route' => 'admin.production.outputs.index', 'permission' => 'production.outputs.view', 'icon' => 'archive', 'active_routes' => ['admin.production.outputs.*']],
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
                    ['key' => 'boms', 'label' => 'Bills of Materials', 'description' => 'Product BOMs and material structures.', 'route' => 'admin.production.boms.index', 'permission' => 'production.bom.view', 'icon' => 'template', 'active_routes' => ['admin.production.boms.*']],
                    ['key' => 'print-templates', 'label' => 'Print Templates', 'description' => 'Print product templates and specifications.', 'route' => 'admin.production.print-templates.index', 'permission' => 'production.bom.view', 'icon' => 'document', 'active_routes' => ['admin.production.print-templates.*']],
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
                    ['key' => 'quality-control', 'label' => 'Quality Control', 'description' => 'Inspection register and approval queue.', 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view', 'icon' => 'badge-check', 'count_key' => 'pending_qc', 'active_routes' => ['admin.production.quality.*']],
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
                    ['key' => 'dispatch-desk', 'label' => 'Dispatch Desk', 'description' => 'Ready jobs and delivery notes — create, dispatch, and confirm in one register.', 'route' => 'admin.dispatch.dashboard', 'permission' => 'dispatch.view', 'icon' => 'truck', 'active_routes' => ['admin.dispatch.dashboard']],
                    ['key' => 'delivery-notes', 'label' => 'Delivery Notes', 'description' => 'Full delivery note register with filters and exports.', 'route' => 'admin.dispatch.delivery-notes.index', 'permission' => 'dispatch.view', 'icon' => 'document-text', 'active_routes' => ['admin.dispatch.delivery-notes.*']],
                    ['key' => 'delivery-calendar', 'label' => 'Delivery Calendar', 'description' => 'Scheduled deliveries by date.', 'route' => 'admin.dispatch.calendar', 'permission' => 'dispatch.view', 'icon' => 'calendar', 'active_routes' => ['admin.dispatch.calendar']],
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
                    ['key' => 'command-center', 'label' => 'Operations Intelligence', 'description' => 'Capacity, pipeline, and maintenance intelligence.', 'route' => 'admin.production.dashboard', 'permission' => 'production.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.production.dashboard']],
                    ['key' => 'operational-registers', 'label' => 'Operational Registers', 'description' => 'Executive daily registers from live ERP data.', 'route' => 'admin.reports.operational-registers', 'permission' => 'reports.view|intelligence.production.view', 'icon' => 'table', 'active_routes' => ['admin.reports.operational-registers', 'admin.reports.operational-registers.print', 'admin.reports.operational-registers.export']],
                    ['key' => 'production-reports', 'label' => 'Production Reports', 'description' => 'Historical performance reporting.', 'route' => 'admin.reports.production', 'permission' => 'reports.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.reports.production', 'admin.reports.production.print']],
                    ['key' => 'production-360', 'label' => 'Production 360', 'description' => 'Strategic production intelligence.', 'route' => 'admin.reports.production360', 'permission' => 'intelligence.production.view|reports.view', 'icon' => 'sparkles', 'active_routes' => ['admin.reports.production360']],
                    ['key' => 'job-costing', 'label' => 'Job Costing & Profitability', 'description' => 'Job-level profitability and cost analysis.', 'route' => 'admin.production.costing.dashboard', 'permission' => 'production.costing.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.production.costing.*', 'admin.production.job-cards.costing']],
                    ['key' => 'job-profitability', 'label' => 'Job Profitability Report', 'description' => 'Top profitable and loss-making jobs.', 'route' => 'admin.reports.job-profitability.index', 'permission' => 'reports.costing.view|reports.view', 'icon' => 'trending-up', 'active_routes' => ['admin.reports.job-profitability.*']],
                ],
            ]],
        ],
    ],
];
