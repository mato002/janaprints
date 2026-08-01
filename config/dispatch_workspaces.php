<?php

return [
    'hub' => [
        [
            'label' => 'Dispatch',
            'description' => 'Delivery planning and fulfillment operations.',
            'route' => 'admin.workspaces.dispatch.section',
            'route_params' => ['section' => 'dispatch'],
            'permission' => 'dispatch.view',
            'icon' => 'truck',
            'active_routes' => ['admin.workspaces.dispatch.section:dispatch', 'admin.dispatch.*'],
        ],
    ],

    'sections' => [
        'dispatch' => [
            'title' => 'Dispatch',
            'description' => 'Delivery notes, dispatch lifecycle, and outbound delivery truth.',
            'icon' => 'truck',
            'quick_actions' => [
                ['label' => 'Open Dispatch Desk', 'route' => 'admin.workspaces.dispatch.section', 'route_params' => ['section' => 'dispatch', 'tab' => 'dispatch-desk'], 'permission' => 'dispatch.view'],
            ],
            'groups' => [[
                'label' => 'Outbound',
                'items' => [
                    [
                        'key' => 'dispatch-desk',
                        'label' => 'Dispatch Desk',
                        'description' => 'Ready jobs and delivery notes in one register.',
                        'route' => 'admin.workspaces.dispatch.section',
                        'route_params' => ['section' => 'dispatch', 'tab' => 'dispatch-desk'],
                        'permission' => 'dispatch.view',
                        'icon' => 'truck',
                        'active_routes' => [
                            'admin.dispatch.dashboard',
                            'admin.dispatch.delivery-notes.*',
                            'admin.dispatch.calendar',
                        ],
                        'modes' => [
                            [
                                'key' => 'desk',
                                'label' => 'Desk',
                                'route' => 'admin.dispatch.dashboard',
                                'active_routes' => ['admin.dispatch.dashboard'],
                            ],
                            [
                                'key' => 'delivery-notes',
                                'label' => 'Delivery notes',
                                'route' => 'admin.dispatch.delivery-notes.index',
                                'active_routes' => ['admin.dispatch.delivery-notes.*'],
                            ],
                            [
                                'key' => 'calendar',
                                'label' => 'Calendar',
                                'route' => 'admin.dispatch.calendar',
                                'active_routes' => ['admin.dispatch.calendar'],
                            ],
                        ],
                    ],
                ],
            ]],
        ],
    ],
];
