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
            'groups' => [[
                'label' => 'Outbound',
                'items' => [
                    ['key' => 'dispatch-dashboard', 'label' => 'Dispatch Dashboard', 'description' => 'Ready jobs, dispatch and delivery metrics.', 'route' => 'admin.dispatch.dashboard', 'permission' => 'dispatch.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.dispatch.dashboard']],
                    ['key' => 'delivery-notes', 'label' => 'Delivery Notes', 'description' => 'Create, dispatch, and confirm deliveries.', 'route' => 'admin.dispatch.delivery-notes.index', 'permission' => 'dispatch.view', 'icon' => 'document-text', 'active_routes' => ['admin.dispatch.delivery-notes.*']],
                    ['key' => 'delivery-calendar', 'label' => 'Delivery Calendar', 'description' => 'Scheduled deliveries calendar.', 'route' => 'admin.dispatch.calendar', 'permission' => 'dispatch.view', 'icon' => 'calendar', 'active_routes' => ['admin.dispatch.calendar']],
                ],
            ]],
        ],
    ],
];
