<?php

/**
 * Production workspace hub and section catalogs (presentation only).
 * Hub: Operations, Planning, Quality, Dispatch.
 * Analytics live under Reports & Intelligence.
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
    ],

    'sections' => [
        'operations' => [
            'title' => 'Operations',
            'description' => 'Run the shop floor from one register.',
            'icon' => 'collection',
            'groups' => [[
                'label' => 'Operations',
                'items' => [
                    [
                        'key' => 'production-floor',
                        'label' => 'Production Floor',
                        'description' => 'Shop floor register with job cards, work-center queue, and finished-goods outputs as desk modes.',
                        'route' => 'admin.workspaces.production.section',
                        'route_params' => ['section' => 'operations', 'tab' => 'production-floor'],
                        'permission' => 'production.view',
                        'icon' => 'view-grid',
                        'count_key' => 'open_jobs',
                        'active_routes' => [
                            'admin.production.floor',
                            'admin.production.home',
                            'admin.production.job-cards.*',
                            'admin.production.queue.*',
                            'admin.production.outputs.*',
                        ],
                        'modes' => [
                            [
                                'key' => 'floor',
                                'label' => 'Run',
                                'route' => 'admin.production.floor',
                                'active_routes' => ['admin.production.floor', 'admin.production.home'],
                            ],
                            [
                                'key' => 'register',
                                'label' => 'Register',
                                'route' => 'admin.production.floor',
                                'route_params' => ['view' => 'register'],
                                'active_routes' => ['admin.production.job-cards.*'],
                            ],
                            [
                                'key' => 'queue',
                                'label' => 'By department',
                                'route' => 'admin.production.floor',
                                'route_params' => ['view' => 'queue'],
                                'active_routes' => ['admin.production.queue.*'],
                            ],
                            [
                                'key' => 'outputs',
                                'label' => 'Outputs',
                                'route' => 'admin.production.floor',
                                'route_params' => ['view' => 'outputs'],
                                'active_routes' => ['admin.production.outputs.*'],
                            ],
                        ],
                    ],
                ],
            ]],
        ],
        'planning' => [
            'title' => 'Planning',
            'description' => 'Scheduling, work centers, and capacity.',
            'icon' => 'calendar',
            'groups' => [
                [
                    'label' => 'Planning',
                    'items' => [
                        ['key' => 'scheduling', 'label' => 'Scheduling', 'description' => 'Production planning and timeline management.', 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view', 'icon' => 'calendar', 'active_routes' => ['admin.production.scheduling.*']],
                        ['key' => 'work-centers', 'label' => 'Work Centers', 'description' => 'Machines, departments and capacity management.', 'route' => 'admin.production.work-centers.index', 'permission' => 'production.work-centers.view', 'icon' => 'chip', 'active_routes' => ['admin.production.work-centers.*']],
                    ],
                ],
                [
                    'label' => 'Setup',
                    'items' => [
                        ['key' => 'boms', 'label' => 'Bills of Materials', 'description' => 'Product BOMs and material structures.', 'route' => 'admin.production.boms.index', 'permission' => 'production.bom.view', 'icon' => 'template', 'active_routes' => ['admin.production.boms.*']],
                        ['key' => 'print-templates', 'label' => 'Print Templates', 'description' => 'Print product templates and specifications.', 'route' => 'admin.production.print-templates.index', 'permission' => 'production.bom.view', 'icon' => 'document', 'active_routes' => ['admin.production.print-templates.*']],
                    ],
                ],
            ],
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
    ],
];
