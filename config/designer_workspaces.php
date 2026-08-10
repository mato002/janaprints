<?php

/**
 * Designer workspace hub and section catalogs (presentation only).
 * Hub: Design — artwork queue, register, and overview.
 */
return [

    'hub' => [
        [
            'label' => 'Design',
            'description' => 'Artwork queue, uploads, and designer workflow.',
            'route' => 'admin.workspaces.designer.section',
            'route_params' => ['section' => 'design'],
            'permission' => 'artwork.view',
            'icon' => 'color-swatch',
            'active_routes' => [
                'admin.workspaces.designer.section:design',
                'admin.artwork.desk',
                'admin.artwork.desk.*',
                'admin.artwork.index',
                'admin.artwork.dashboard',
            ],
        ],
    ],

    'sections' => [
        'design' => [
            'title' => 'Design',
            'description' => 'Claim jobs, upload softcopies, and track artwork through approval.',
            'icon' => 'color-swatch',
            'quick_actions' => [
                ['label' => 'Open Queue', 'route' => 'admin.workspaces.designer.section', 'route_params' => ['section' => 'design', 'tab' => 'designer-desk'], 'permission' => 'artwork.view'],
            ],
            'groups' => [[
                'label' => 'Design',
                'items' => [
                    [
                        'key' => 'designer-desk',
                        'label' => 'Designer Desk',
                        'description' => 'Artwork queue, uploads, and designer workflow.',
                        'route' => 'admin.artwork.desk',
                        'permission' => 'artwork.view',
                        'icon' => 'color-swatch',
                        'active_routes' => [
                            'admin.artwork.desk',
                            'admin.artwork.desk.*',
                            'admin.artwork.show',
                            'admin.artwork.edit',
                        ],
                        'modes' => [
                            [
                                'key' => 'queue',
                                'label' => 'Queue',
                                'route' => 'admin.artwork.desk',
                                'route_params' => ['filter' => 'all'],
                                'active_routes' => ['admin.artwork.desk', 'admin.artwork.desk.*'],
                            ],
                            [
                                'key' => 'available',
                                'label' => 'Available',
                                'route' => 'admin.artwork.desk',
                                'route_params' => ['filter' => 'available'],
                                'active_routes' => ['admin.artwork.desk', 'admin.artwork.desk.*'],
                            ],
                            [
                                'key' => 'mine',
                                'label' => 'Mine',
                                'route' => 'admin.artwork.desk',
                                'route_params' => ['filter' => 'mine'],
                                'active_routes' => ['admin.artwork.desk', 'admin.artwork.desk.*'],
                            ],
                            [
                                'key' => 'working',
                                'label' => 'Working',
                                'route' => 'admin.artwork.desk',
                                'route_params' => ['filter' => 'working'],
                                'active_routes' => ['admin.artwork.desk', 'admin.artwork.desk.*'],
                            ],
                            [
                                'key' => 'review',
                                'label' => 'Review',
                                'route' => 'admin.artwork.desk',
                                'route_params' => ['filter' => 'review'],
                                'active_routes' => ['admin.artwork.desk', 'admin.artwork.desk.*'],
                            ],
                        ],
                    ],
                    [
                        'key' => 'artwork-requests',
                        'label' => 'All Requests',
                        'description' => 'Full artwork request register.',
                        'route' => 'admin.artwork.index',
                        'permission' => 'artwork.view',
                        'icon' => 'document-text',
                        'active_routes' => ['admin.artwork.index'],
                    ],
                    [
                        'key' => 'artwork-overview',
                        'label' => 'Overview',
                        'description' => 'Artwork pipeline summary and KPIs.',
                        'route' => 'admin.artwork.dashboard',
                        'permission' => 'artwork.view',
                        'icon' => 'chart-pie',
                        'active_routes' => ['admin.artwork.dashboard'],
                    ],
                ],
            ]],
        ],
    ],

];
