<?php

/**
 * Commercial workspace hub and section catalogs (presentation only).
 * Root hub shows five workspace cards; features live on section pages.
 */
return [

    'hub' => [
        [
            'label' => 'CRM',
            'description' => 'Customers, leads, segments, and commercial activities.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'crm'],
            'permission' => 'crm.customers.view|crm.leads.view|crm.activities.view',
            'icon' => 'user-circle',
            'active_routes' => ['admin.workspaces.commercial.section:crm', 'admin.crm.*', 'admin.commercial.activities.*'],
        ],
        [
            'label' => 'Sales',
            'description' => 'Quotations, artwork, sales orders, price books, and approvals.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'sales'],
            'permission' => 'quotations.view|artwork.view|sales_orders.view',
            'icon' => 'document-text',
            'active_routes' => ['admin.workspaces.commercial.section:sales', 'admin.quotations.*', 'admin.artwork.*', 'admin.sales-orders.*'],
        ],
        [
            'label' => 'Customer Service',
            'description' => 'Complaints, support tickets, statements, and customer history.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'customer-service'],
            'permission' => 'crm.customers.view|receivables.statement.view',
            'icon' => 'inbox',
            'active_routes' => ['admin.workspaces.commercial.section:customer-service', 'admin.receivables.statement'],
        ],
        [
            'label' => 'Point Of Sale',
            'description' => 'Counter sales, returns, POS sessions, and cash reconciliation.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'point-of-sale'],
            'permission' => 'pos.view',
            'icon' => 'cash',
            'active_routes' => ['admin.workspaces.commercial.section:point-of-sale', 'admin.commercial.pos.*'],
        ],
        [
            'label' => 'Reports',
            'description' => 'Sales, quotation, customer, artwork, and conversion analytics.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'reports'],
            'permission' => 'quotations.view|crm.customers.view|artwork.view|sales_orders.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.commercial.section:reports'],
        ],
    ],

    'sections' => [

        'crm' => [
            'title' => 'CRM',
            'description' => 'Customer relationships, pipeline leads, segments, and touchpoint activities.',
            'icon' => 'user-circle',
            'groups' => [
                [
                    'label' => 'CRM',
                    'items' => [
                        ['label' => 'Customers', 'description' => 'Customer accounts, contacts, and commercial history.', 'route' => 'admin.crm.customers.index', 'permission' => 'crm.customers.view', 'icon' => 'user-circle', 'active_routes' => ['admin.crm.customers.*']],
                        ['label' => 'Leads', 'description' => 'Pipeline leads, follow-ups, and conversion tracking.', 'route' => 'admin.crm.leads.index', 'permission' => 'crm.leads.view', 'icon' => 'sparkles', 'active_routes' => ['admin.crm.leads.*']],
                        ['label' => 'Segments', 'description' => 'Customer segments for targeting and reporting.', 'route' => 'admin.crm.segments.index', 'permission' => 'crm.customers.view', 'icon' => 'tag', 'active_routes' => ['admin.crm.segments.*']],
                        ['label' => 'Activities', 'description' => 'Calls, meetings, and customer touchpoints.', 'route' => 'admin.commercial.activities.index', 'permission' => 'crm.activities.view', 'icon' => 'clock', 'active_routes' => ['admin.commercial.activities.*']],
                    ],
                ],
            ],
        ],

        'sales' => [
            'title' => 'Sales',
            'description' => 'Quotations, artwork, orders, pricing, and approval workflows.',
            'icon' => 'document-text',
            'groups' => [
                [
                    'label' => 'Sales',
                    'items' => [
                        ['label' => 'Quotations', 'description' => 'Quotes, pricing, and customer proposals.', 'route' => 'admin.quotations.dashboard', 'permission' => 'quotations.view', 'icon' => 'document-text', 'active_routes' => ['admin.quotations.*']],
                        ['label' => 'Artwork', 'description' => 'Design requests, proofs, and approvals.', 'route' => 'admin.artwork.dashboard', 'permission' => 'artwork.view', 'icon' => 'color-swatch', 'active_routes' => ['admin.artwork.*']],
                        ['label' => 'Sales Orders', 'description' => 'Confirmed orders ready for production.', 'route' => 'admin.sales-orders.dashboard', 'permission' => 'sales_orders.view', 'icon' => 'clipboard-list', 'active_routes' => ['admin.sales-orders.*']],
                        ['label' => 'Price Books', 'description' => 'Commercial price lists and customer pricing tiers.', 'coming_soon' => true, 'icon' => 'tag'],
                        ['label' => 'Approvals', 'description' => 'Quote and order approval queues.', 'coming_soon' => true, 'icon' => 'badge-check'],
                    ],
                ],
            ],
        ],

        'customer-service' => [
            'title' => 'Customer Service',
            'description' => 'Complaints, support cases, statements, and service history.',
            'icon' => 'inbox',
            'groups' => [
                [
                    'label' => 'Customer Service',
                    'items' => [
                        ['label' => 'Complaints', 'description' => 'Customer complaints and resolution tracking.', 'coming_soon' => true, 'icon' => 'exclamation'],
                        ['label' => 'Support Tickets', 'description' => 'Help desk tickets and case management.', 'coming_soon' => true, 'icon' => 'inbox'],
                        ['label' => 'Customer Statements', 'description' => 'Period statements of account.', 'route' => 'admin.receivables.statement', 'permission' => 'receivables.statement.view', 'icon' => 'document-text', 'active_routes' => ['admin.receivables.statement']],
                        ['label' => 'Customer 360', 'description' => 'Enterprise customer workspace — commercial, conversations, and timeline.', 'route' => 'admin.crm.customers.index', 'permission' => 'crm.customers.view', 'icon' => 'clock', 'active_routes' => ['admin.crm.customers.*']],
                    ],
                ],
            ],
        ],

        'point-of-sale' => [
            'title' => 'Point Of Sale',
            'description' => 'Retail counter sales, returns, sessions, and cash control.',
            'icon' => 'cash',
            'groups' => [
                [
                    'label' => 'Point Of Sale',
                    'items' => [
                        ['label' => 'Counter Sales', 'description' => 'Counter checkout and daily retail sales.', 'route' => 'admin.commercial.pos.dashboard', 'permission' => 'pos.view', 'icon' => 'cash', 'active_routes' => ['admin.commercial.pos.*']],
                        ['label' => 'Returns', 'description' => 'POS refunds and return processing.', 'coming_soon' => true, 'icon' => 'switch-horizontal'],
                        ['label' => 'POS Sessions', 'description' => 'Open and close cashier sessions.', 'coming_soon' => true, 'icon' => 'clock'],
                        ['label' => 'Cash Reconciliation', 'description' => 'End-of-day cash counts and variances.', 'coming_soon' => true, 'icon' => 'scale'],
                    ],
                ],
            ],
        ],

        'reports' => [
            'title' => 'Reports',
            'description' => 'Commercial analytics across sales, CRM, artwork, and conversion.',
            'icon' => 'chart-pie',
            'groups' => [
                [
                    'label' => 'Reports',
                    'items' => [
                        ['label' => 'Sales Reports', 'description' => 'Revenue, orders, and sales performance.', 'coming_soon' => true, 'icon' => 'chart-bar'],
                        ['label' => 'Quotation Reports', 'description' => 'Quote pipeline, win rates, and value.', 'coming_soon' => true, 'icon' => 'document-text'],
                        ['label' => 'Customer Reports', 'description' => 'CRM activity and customer analytics.', 'coming_soon' => true, 'icon' => 'user-circle'],
                        ['label' => 'Artwork Reports', 'description' => 'Design throughput and approval metrics.', 'coming_soon' => true, 'icon' => 'color-swatch'],
                        ['label' => 'Conversion Reports', 'description' => 'Lead-to-quote and quote-to-order conversion.', 'coming_soon' => true, 'icon' => 'sparkles'],
                    ],
                ],
            ],
        ],

    ],

];
