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
            'permission' => 'pos.view|commercial.pos.sessions.view',
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
            'active_routes' => ['admin.workspaces.commercial.section:reports', 'commercial.reports.sales.*', 'commercial.reports.quotations.*', 'commercial.reports.sales_orders.*', 'commercial.reports.customers.*', 'commercial.reports.artwork.*', 'commercial.reports.conversion.*'],
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
            'permission' => 'pos.view|commercial.pos.sessions.view',
            'groups' => [
                [
                    'label' => 'Point Of Sale',
                    'items' => [
                        ['label' => 'Counter Sales', 'description' => 'Counter checkout and daily retail sales.', 'route' => 'admin.commercial.pos.dashboard', 'permission' => 'pos.view|pos.counter_sales.view', 'icon' => 'cash', 'active_routes' => ['admin.commercial.pos.dashboard', 'admin.commercial.pos.counter-sales', 'admin.commercial.pos.create', 'admin.commercial.pos.index', 'admin.commercial.pos.show', 'admin.commercial.pos.holds', 'admin.commercial.pos.resume', 'admin.commercial.pos.pay']],
                        ['label' => 'POS Sessions', 'description' => 'Open and close cashier sessions.', 'route' => 'admin.commercial.pos.sessions.index', 'permission' => 'commercial.pos.sessions.view', 'icon' => 'clock', 'active_routes' => ['admin.commercial.pos.sessions.*']],
                        ['label' => 'Cash Reconciliation', 'description' => 'End-of-day cash counts, variances, and approval.', 'route' => 'admin.commercial.pos.reconciliation.index', 'permission' => 'commercial.pos.reconciliation.view', 'icon' => 'scale', 'active_routes' => ['admin.commercial.pos.reconciliation.*']],
                        ['label' => 'Returns', 'description' => 'POS refunds and return processing.', 'route' => 'admin.commercial.pos.returns.dashboard', 'permission' => 'commercial.pos.returns.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.commercial.pos.returns.*']],
                        ['label' => 'POS Intelligence', 'description' => 'Departmental POS sales, sessions, payments, and returns analytics.', 'route' => 'commercial.pos.reports.index', 'permission' => 'commercial.pos.reports.view', 'icon' => 'chart-pie', 'active_routes' => ['commercial.pos.reports.*']],
                        ['label' => 'POS Certification', 'description' => 'Operational certification for inventory, accounting, cash, returns, and session truth.', 'route' => 'admin.commercial.pos.certification.index', 'permission' => 'commercial.pos.certification.view', 'icon' => 'shield-check', 'active_routes' => ['admin.commercial.pos.certification.*']],
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
                        ['label' => 'Sales Reports', 'description' => 'Revenue, orders, and sales performance.', 'route' => 'commercial.reports.sales.index', 'permission' => 'commercial.reports.sales.view', 'icon' => 'chart-bar', 'active_routes' => ['commercial.reports.sales.*']],
                        ['label' => 'Quotation Reports', 'description' => 'Quote pipeline, win rates, and value.', 'route' => 'commercial.reports.quotations.index', 'permission' => 'commercial.reports.quotations.view', 'icon' => 'document-text', 'active_routes' => ['commercial.reports.quotations.*']],
                        ['label' => 'Sales Order Reports', 'description' => 'Order pipeline, status, aging, and conversion.', 'route' => 'commercial.reports.sales_orders.index', 'permission' => 'commercial.reports.sales_orders.view', 'icon' => 'clipboard-list', 'active_routes' => ['commercial.reports.sales_orders.*']],
                        ['label' => 'Customer Reports', 'description' => 'Customer counts, revenue, activity, and growth analytics.', 'route' => 'commercial.reports.customers.index', 'permission' => 'commercial.reports.customers.view', 'icon' => 'user-circle', 'active_routes' => ['commercial.reports.customers.*']],
                        ['label' => 'Artwork Reports', 'description' => 'Design throughput and approval metrics.', 'route' => 'commercial.reports.artwork.index', 'permission' => 'commercial.reports.artwork.view', 'icon' => 'color-swatch', 'active_routes' => ['commercial.reports.artwork.*']],
                        ['label' => 'Conversion Reports', 'description' => 'Lead-to-quote and quote-to-order conversion.', 'route' => 'commercial.reports.conversion.index', 'permission' => 'commercial.reports.conversion.view', 'icon' => 'sparkles', 'active_routes' => ['commercial.reports.conversion.*']],
                        ['label' => 'Export History', 'description' => 'Queued commercial report exports and downloads.', 'route' => 'commercial.reports.exports.index', 'permission' => 'commercial.reports.exports.view', 'icon' => 'arrow-down-tray', 'active_routes' => ['commercial.reports.exports.*']],
                    ],
                ],
            ],
        ],

    ],

];
