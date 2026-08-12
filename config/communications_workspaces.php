<?php

return [
    'hub' => [
        [
            'label' => 'Email',
            'description' => 'Compose and track customer email.',
            'route' => 'admin.workspaces.communications.section',
            'route_params' => ['section' => 'email'],
            'permission' => 'communications.email.view',
            'icon' => 'mail',
            'active_routes' => ['admin.workspaces.communications.section:email', 'admin.communications.email.*'],
        ],
        [
            'label' => 'WhatsApp',
            'description' => 'WhatsApp inbox, templates, and delivery.',
            'route' => 'admin.workspaces.communications.section',
            'route_params' => ['section' => 'whatsapp'],
            'permission' => 'communications.whatsapp.view',
            'icon' => 'inbox',
            'active_routes' => ['admin.workspaces.communications.section:whatsapp', 'admin.communications.whatsapp.*'],
        ],
        [
            'label' => 'SMS',
            'description' => 'SMS campaigns, queues, credits, and provider logs.',
            'route' => 'admin.workspaces.communications.section',
            'route_params' => ['section' => 'sms'],
            'permission' => 'communications.sms.view',
            'icon' => 'chat',
            'active_routes' => ['admin.workspaces.communications.section:sms', 'admin.communications.sms.*'],
        ],
        [
            'label' => 'Templates',
            'description' => 'Reusable message templates.',
            'route' => 'admin.workspaces.communications.section',
            'route_params' => ['section' => 'templates'],
            'permission' => 'communications.templates.view',
            'icon' => 'document-text',
            'active_routes' => ['admin.workspaces.communications.section:templates', 'admin.communications.templates.*'],
        ],
        [
            'label' => 'Shared Inbox',
            'description' => 'Shared team inbox and internal notifications.',
            'route' => 'admin.workspaces.communications.section',
            'route_params' => ['section' => 'inbox'],
            'permission' => 'communications.inbox.view|communications.notifications.view',
            'icon' => 'inbox',
            'active_routes' => ['admin.workspaces.communications.section:inbox', 'admin.communications.inbox.*', 'admin.communications.notifications.*'],
        ],
        [
            'label' => 'Logs',
            'description' => 'Communication truth ledger across channels.',
            'route' => 'admin.workspaces.communications.section',
            'route_params' => ['section' => 'logs'],
            'permission' => 'communications.logs.view',
            'icon' => 'clock',
            'active_routes' => ['admin.workspaces.communications.section:logs', 'admin.communications.logs.*'],
        ],
    ],

    'sections' => [
        'sms' => [
            'title' => 'SMS',
            'description' => 'SMS operations and analytics.',
            'icon' => 'chat',
            'groups' => [[
                'label' => 'SMS',
                'items' => [
                    ['key' => 'sms-dashboard', 'label' => 'SMS Dashboard', 'description' => 'Top up credits, delivery health, and issues that need attention.', 'route' => 'admin.communications.sms.dashboard', 'permission' => 'communications.sms.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.communications.sms.dashboard']],
                    ['key' => 'campaigns', 'label' => 'SMS Campaigns', 'description' => 'Create, preview, schedule, and send bulk SMS.', 'route' => 'admin.communications.sms.campaigns.index', 'permission' => 'communications.sms.view', 'icon' => 'sparkles', 'active_routes' => ['admin.communications.sms.campaigns.*']],
                    ['key' => 'queue', 'label' => 'SMS Queue', 'description' => 'Queued, processing, sent, and failed messages.', 'route' => 'admin.communications.sms.queues.index', 'permission' => 'communications.sms.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.communications.sms.queues.*']],
                    ['key' => 'provider-logs', 'label' => 'Provider Logs', 'description' => 'SMS provider request and response audit trail.', 'route' => 'admin.communications.sms.provider-logs.index', 'permission' => 'communications.sms.audit', 'icon' => 'clock', 'active_routes' => ['admin.communications.sms.provider-logs.*']],
                    ['key' => 'credits', 'label' => 'SMS Credit Ledger', 'description' => 'Credits, purchases, usage, and balances.', 'route' => 'admin.communications.sms.credits.index', 'permission' => 'communications.sms.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.communications.sms.credits.*']],
                ],
            ]],
        ],
        'whatsapp' => [
            'title' => 'WhatsApp',
            'description' => 'WhatsApp conversations and templates.',
            'icon' => 'inbox',
            'groups' => [[
                'label' => 'WhatsApp',
                'items' => [
                    ['key' => 'whatsapp-inbox', 'label' => 'WhatsApp', 'description' => 'Conversation center — inbox, templates, and delivery.', 'route' => 'admin.communications.whatsapp.inbox', 'permission' => 'communications.whatsapp.view', 'icon' => 'inbox', 'active_routes' => ['admin.communications.whatsapp.*']],
                ],
            ]],
        ],
        'email' => [
            'title' => 'Email',
            'description' => 'Sales email workspace.',
            'icon' => 'mail',
            'groups' => [[
                'label' => 'Email',
                'items' => [
                    ['key' => 'email-center', 'label' => 'Mailbox', 'description' => 'Compose, sent, drafts, and customer follow-ups.', 'route' => 'admin.communications.email.dashboard', 'permission' => 'communications.email.view', 'icon' => 'mail', 'active_routes' => [
                        'admin.communications.email.dashboard',
                        'admin.communications.email.sent.*',
                        'admin.communications.email.drafts.*',
                        'admin.communications.email.queue.*',
                        'admin.communications.email.inbox.*',
                        'admin.communications.email.customers.*',
                        'admin.communications.email.compose*',
                        'admin.communications.email.templates.*',
                        'admin.communications.email.messages.*',
                    ]],
                    ['key' => 'email-operations', 'label' => 'Operations', 'description' => 'Delivery health, volume, queue, and infrastructure metrics.', 'route' => 'admin.communications.email.analytics', 'permission' => 'communications.email.manage|communications.email.audit', 'icon' => 'chart-pie', 'active_routes' => [
                        'admin.communications.email.analytics',
                        'admin.communications.email.reports.*',
                        'admin.communications.email.delivery.*',
                        'admin.communications.email.settings',
                        'admin.communications.email.certification',
                        'admin.communications.email.campaigns.*',
                    ]],
                ],
            ]],
        ],
        'templates' => [
            'title' => 'Templates',
            'description' => 'Reusable message templates.',
            'icon' => 'document-text',
            'groups' => [[
                'label' => 'Templates',
                'items' => [
                    ['key' => 'templates', 'label' => 'Templates', 'description' => 'Reusable message templates with versioning and preview.', 'route' => 'admin.communications.templates.index', 'permission' => 'communications.templates.view', 'icon' => 'document-text', 'active_routes' => ['admin.communications.templates.*']],
                ],
            ]],
        ],
        'inbox' => [
            'title' => 'Shared Inbox',
            'description' => 'Shared inbox and notifications.',
            'icon' => 'inbox',
            'groups' => [[
                'label' => 'Shared Inbox',
                'items' => [
                    ['key' => 'shared-inbox', 'label' => 'Shared Inbox', 'description' => 'WhatsApp-style team inbox — threads, notes, handover, and CEO view.', 'route' => 'admin.communications.inbox.index', 'permission' => 'communications.inbox.view', 'icon' => 'inbox', 'active_routes' => ['admin.communications.inbox.*']],
                    ['key' => 'notifications', 'label' => 'Notification Center', 'description' => 'Internal ERP alerts, preferences, and notification history.', 'route' => 'admin.communications.notifications.index', 'permission' => 'communications.notifications.view', 'icon' => 'bell', 'active_routes' => ['admin.communications.notifications.*']],
                ],
            ]],
        ],
        'logs' => [
            'title' => 'Logs',
            'description' => 'Communication audit trail.',
            'icon' => 'clock',
            'groups' => [[
                'label' => 'Logs',
                'items' => [
                    ['key' => 'communication-logs', 'label' => 'Communication Logs', 'description' => 'Communication truth ledger — all channels in one timeline.', 'route' => 'admin.communications.logs.dashboard', 'permission' => 'communications.logs.view', 'icon' => 'clock', 'active_routes' => ['admin.communications.logs.*']],
                ],
            ]],
        ],
    ],
];
