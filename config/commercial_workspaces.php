<?php

/**
 * Commercial workspace hub and section catalogs (presentation only).
 * Root hub shows five workspace cards; features live on section pages.
 * Order: daily operations first → support → analysis last.
 */
return [

    'hub' => [
        [
            'label' => 'Sales',
            'description' => 'Quotations, artwork, sales orders, price books, and approvals.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'sales'],
            'permission' => 'quotations.view|artwork.view|sales_orders.view',
            'icon' => 'document-text',
            'active_routes' => ['admin.workspaces.commercial.section:sales', 'admin.quotations.*', 'admin.artwork.*', 'admin.sales-orders.*', 'admin.sales.desk', 'admin.sales.desk.*'],
        ],
        [
            'label' => 'Customer Service',
            'description' => 'Complaints, support tickets, statements, and customer history.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'customer-service'],
            'permission' => 'public_leads.quote_requests.view|public_leads.contact_messages.view|commercial.complaints.view|commercial.tickets.view|receivables.statement.view',
            'icon' => 'inbox',
            'active_routes' => ['admin.workspaces.commercial.section:customer-service', 'admin.receivables.statement', 'admin.public-quote-requests.*', 'admin.public-contact-messages.*', 'admin.commercial.complaints.*', 'admin.commercial.support-tickets.*', 'admin.dispatch.dashboard', 'admin.dispatch.delivery-notes.*'],
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
            'label' => 'CRM',
            'description' => 'Customers, leads, segments, and commercial activities.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'crm'],
            'permission' => 'crm.customers.view|crm.leads.view|commercial.activities.view',
            'icon' => 'user-circle',
            'active_routes' => ['admin.workspaces.commercial.section:crm', 'admin.crm.*', 'admin.commercial.activities.*'],
        ],
        [
            'label' => 'Reports',
            'description' => 'Sales, quotation, customer, artwork, and conversion analytics.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'reports'],
            'permission' => 'quotations.view|crm.customers.view|artwork.view|sales_orders.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.commercial.section:reports', 'admin.commercial.reports.sales.*', 'admin.commercial.reports.quotations.*', 'admin.commercial.reports.sales_orders.*', 'admin.commercial.reports.customers.*', 'admin.commercial.reports.artwork.*', 'admin.commercial.reports.conversion.*'],
        ],
    ],

    'sections' => [

        'crm' => [
            'title' => 'CRM',
            'description' => 'Customer relationships, pipeline leads, segments, and touchpoint activities.',
            'icon' => 'user-circle',
            'quick_actions' => [
                ['label' => 'Create Customer', 'route' => 'admin.crm.customers.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'crm.customers.create'],
                ['label' => 'Create Lead', 'route' => 'admin.crm.leads.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'crm.leads.create'],
                ['label' => 'Create Segment', 'route' => 'admin.crm.segments.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'crm.customers.create'],
                ['label' => 'Log Activity', 'route' => 'admin.commercial.activities.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'commercial.activities.create'],
                ['label' => 'Open CRM Dashboard', 'route' => 'admin.crm.dashboard', 'permission' => 'crm.customers.view'],
            ],
            'groups' => [
                [
                    'label' => 'CRM',
                    'items' => [
                        [
                            'label' => 'Customers',
                            'description' => 'Customer accounts, contacts, and commercial history.',
                            'route' => 'admin.crm.customers.index',
                            'permission' => 'crm.customers.view',
                            'icon' => 'user-circle',
                            'active_routes' => ['admin.crm.customers.*'],
                            'toolbar_actions' => [
                                [
                                    'label' => 'Create customer',
                                    'route' => 'admin.crm.customers.create',
                                    'route_params' => ['from' => 'commercial'],
                                    'permission' => 'crm.customers.create',
                                    'modal' => true,
                                ],
                            ],
                        ],
                        ['label' => 'Leads', 'description' => 'Pipeline leads, follow-ups, and conversion tracking.', 'route' => 'admin.crm.leads.index', 'permission' => 'crm.leads.view', 'icon' => 'sparkles', 'active_routes' => ['admin.crm.leads.*']],
                        ['label' => 'Activities', 'description' => 'Calls, meetings, and customer touchpoints.', 'route' => 'admin.commercial.activities.index', 'permission' => 'commercial.activities.view', 'icon' => 'clock', 'active_routes' => ['admin.commercial.activities.*']],
                        ['label' => 'Segments', 'description' => 'Customer segments for targeting and reporting.', 'route' => 'admin.crm.segments.index', 'permission' => 'crm.customers.view', 'icon' => 'tag', 'active_routes' => ['admin.crm.segments.*']],
                    ],
                ],
            ],
        ],

        'sales' => [
            'title' => 'Sales',
            'description' => 'Quotations, artwork, orders, pricing, and approval workflows.',
            'icon' => 'document-text',
            'quick_actions' => [
                ['label' => 'Open Sales Desk', 'route' => 'admin.sales.desk', 'route_params' => ['from' => 'commercial'], 'permission' => 'crm.customers.create|sales_orders.create'],
                ['label' => 'Create Quotation', 'route' => 'admin.quotations.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'quotations.create'],
                ['label' => 'Create Direct Order', 'route' => 'admin.sales-orders.create', 'route_params' => ['tab' => 'direct'], 'permission' => 'sales_orders.create'],
                ['label' => 'Create Artwork Request', 'route' => 'admin.artwork.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'artwork.create'],
                ['label' => 'Open Sales Orders', 'route' => 'admin.sales-orders.index', 'permission' => 'sales_orders.view'],
                ['label' => 'Open Approvals', 'route' => 'admin.commercial.approvals.index', 'permission' => 'commercial.approvals.view'],
                ['label' => 'Open Price Books', 'route' => 'admin.commercial.price-books.index', 'permission' => 'commercial.price_books.view'],
            ],
            'sales_note' => 'Use quotations for new customer work, or direct orders with print specifications for repeat production.',
            'groups' => [
                [
                    'label' => 'Sales',
                    'items' => [
                        ['key' => 'sales-desk', 'label' => 'Sales Desk', 'description' => 'Walk-in flow: customer, specification, order, and release to production.', 'route' => 'admin.sales.desk', 'permission' => 'crm.customers.create|sales_orders.create', 'icon' => 'shopping-cart', 'active_routes' => ['admin.sales.desk', 'admin.sales.desk.*'], 'open_full' => true],
                        ['label' => 'Quotations', 'description' => 'Quotes, pricing, and customer proposals.', 'route' => 'admin.quotations.index', 'permission' => 'quotations.view', 'icon' => 'document-text', 'active_routes' => ['admin.quotations.*']],
                        ['label' => 'Artwork', 'description' => 'Design requests, proofs, and approvals.', 'route' => 'admin.artwork.index', 'permission' => 'artwork.view', 'icon' => 'color-swatch', 'active_routes' => ['admin.artwork.*']],
                        ['label' => 'Sales Orders', 'description' => 'Confirmed orders ready for production.', 'route' => 'admin.sales-orders.index', 'permission' => 'sales_orders.view', 'icon' => 'clipboard-list', 'active_routes' => ['admin.sales-orders.*']],
                        ['label' => 'Approvals', 'description' => 'Quote and order approval queues.', 'route' => 'admin.commercial.approvals.index', 'permission' => 'commercial.approvals.view', 'icon' => 'badge-check', 'active_routes' => ['admin.commercial.approvals.*']],
                        ['label' => 'Price Books', 'description' => 'Commercial price lists and customer pricing tiers.', 'route' => 'admin.commercial.price-books.index', 'permission' => 'commercial.price_books.view', 'icon' => 'tag', 'active_routes' => ['admin.commercial.price-books.*']],
                    ],
                ],
            ],
        ],

        'customer-service' => [
            'title' => 'Customer Service',
            'description' => 'Complaints, support cases, statements, and service history.',
            'icon' => 'inbox',
            'quick_actions' => [
                ['label' => 'Create Complaint', 'route' => 'admin.commercial.complaints.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'commercial.complaints.create'],
                ['label' => 'Create Support Ticket', 'route' => 'admin.commercial.support-tickets.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'commercial.tickets.create'],
            ],
            'groups' => [
                [
                    'label' => 'Customer Service',
                    'items' => [
                        ['label' => 'Quote Requests', 'description' => 'Public storefront quote requests from guest visitors.', 'route' => 'admin.public-quote-requests.index', 'permission' => 'public_leads.quote_requests.view', 'icon' => 'document-text', 'active_routes' => ['admin.public-quote-requests.*'], 'count_key' => 'pending_quote_requests'],
                        ['label' => 'Contact Messages', 'description' => 'Public contact form messages from the storefront.', 'route' => 'admin.public-contact-messages.index', 'permission' => 'public_leads.contact_messages.view', 'icon' => 'inbox', 'active_routes' => ['admin.public-contact-messages.*'], 'count_key' => 'unread_contact_messages'],
                        ['label' => 'Support Tickets', 'description' => 'Help desk tickets and case management.', 'route' => 'admin.commercial.support-tickets.index', 'permission' => 'commercial.tickets.view', 'icon' => 'inbox', 'active_routes' => ['admin.commercial.support-tickets.*']],
                        ['label' => 'Complaints', 'description' => 'Customer complaints and resolution tracking.', 'route' => 'admin.commercial.complaints.index', 'permission' => 'commercial.complaints.view', 'icon' => 'exclamation', 'active_routes' => ['admin.commercial.complaints.*']],
                        ['label' => 'Dispatch Desk', 'description' => 'Outbound delivery — package, courier, collect, and confirm delivery.', 'route' => 'admin.dispatch.dashboard', 'permission' => 'dispatch.view', 'icon' => 'truck', 'active_routes' => ['admin.dispatch.dashboard', 'admin.dispatch.delivery-notes.*', 'admin.dispatch.calendar']],
                        ['label' => 'Customer Statements', 'description' => 'Period statements of account.', 'route' => 'admin.receivables.statement', 'permission' => 'receivables.statement.view', 'icon' => 'document-text', 'active_routes' => ['admin.receivables.statement']],
                        ['label' => 'Customer 360', 'description' => 'Select a customer to view profile, quotations, jobs, invoices, payments, communications, and timeline.', 'route' => 'admin.crm.customers.index', 'permission' => 'crm.customers.view', 'icon' => 'clock', 'skip_desk_redirect' => true, 'active_routes' => ['admin.crm.customers.*']],
                    ],
                ],
            ],
        ],

        'point-of-sale' => [
            'title' => 'Point Of Sale',
            'description' => 'Retail counter sales, returns, sessions, and cash control.',
            'icon' => 'cash',
            'permission' => 'pos.view|commercial.pos.sessions.view',
            'quick_actions' => [
                ['label' => 'New Counter Sale', 'route' => 'admin.commercial.pos.counter-sales', 'route_params' => ['from' => 'commercial'], 'permission' => 'pos.create|pos.counter_sales.create'],
                ['label' => 'New POS Return', 'route' => 'admin.commercial.pos.returns.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'commercial.pos.returns.create'],
                ['label' => 'Open POS Sessions', 'route' => 'admin.commercial.pos.sessions.index', 'permission' => 'commercial.pos.sessions.view'],
                ['label' => 'Open Cash Reconciliation', 'route' => 'admin.commercial.pos.reconciliation.index', 'permission' => 'commercial.pos.reconciliation.view'],
            ],
            'groups' => [
                [
                    'label' => 'Point Of Sale',
                    'items' => [
                        ['label' => 'Counter Sales', 'description' => 'Counter checkout and daily retail sales.', 'route' => 'admin.commercial.pos.dashboard', 'permission' => 'pos.view|pos.counter_sales.view', 'icon' => 'cash', 'active_routes' => ['admin.commercial.pos.dashboard', 'admin.commercial.pos.counter-sales', 'admin.commercial.pos.index', 'admin.commercial.pos.show', 'admin.commercial.pos.holds', 'admin.commercial.pos.resume', 'admin.commercial.pos.pay']],
                        ['label' => 'POS Sessions', 'description' => 'Open and close cashier sessions.', 'route' => 'admin.commercial.pos.sessions.index', 'permission' => 'commercial.pos.sessions.view', 'icon' => 'clock', 'active_routes' => ['admin.commercial.pos.sessions.*']],
                        ['label' => 'Cash Reconciliation', 'description' => 'End-of-day cash counts, variances, and approval.', 'route' => 'admin.commercial.pos.reconciliation.index', 'permission' => 'commercial.pos.reconciliation.view', 'icon' => 'scale', 'active_routes' => ['admin.commercial.pos.reconciliation.*']],
                        ['label' => 'Returns', 'description' => 'POS refunds and return processing.', 'route' => 'admin.commercial.pos.returns.dashboard', 'permission' => 'commercial.pos.returns.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.commercial.pos.returns.*']],
                        ['label' => 'POS Intelligence', 'description' => 'Departmental POS sales, sessions, payments, and returns analytics.', 'route' => 'admin.commercial.pos.reports.index', 'permission' => 'commercial.pos.reports.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.commercial.pos.reports.*']],
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
                        ['label' => 'Sales Reports', 'description' => 'Revenue, orders, and sales performance.', 'route' => 'admin.commercial.reports.sales.index', 'permission' => 'commercial.reports.sales.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.commercial.reports.sales.*']],
                        ['label' => 'Quotation Reports', 'description' => 'Quote pipeline, win rates, and value.', 'route' => 'admin.commercial.reports.quotations.index', 'permission' => 'commercial.reports.quotations.view', 'icon' => 'document-text', 'active_routes' => ['admin.commercial.reports.quotations.*']],
                        ['label' => 'Sales Order Reports', 'description' => 'Order pipeline, status, aging, and conversion.', 'route' => 'admin.commercial.reports.sales_orders.index', 'permission' => 'commercial.reports.sales_orders.view', 'icon' => 'clipboard-list', 'active_routes' => ['admin.commercial.reports.sales_orders.*']],
                        ['label' => 'Customer Reports', 'description' => 'Customer counts, revenue, activity, and growth analytics.', 'route' => 'admin.commercial.reports.customers.index', 'permission' => 'commercial.reports.customers.view', 'icon' => 'user-circle', 'active_routes' => ['admin.commercial.reports.customers.*']],
                        ['label' => 'Artwork Reports', 'description' => 'Design throughput and approval metrics.', 'route' => 'admin.commercial.reports.artwork.index', 'permission' => 'commercial.reports.artwork.view', 'icon' => 'color-swatch', 'active_routes' => ['admin.commercial.reports.artwork.*']],
                        ['label' => 'Conversion Reports', 'description' => 'Lead-to-quote and quote-to-order conversion.', 'route' => 'admin.commercial.reports.conversion.index', 'permission' => 'commercial.reports.conversion.view', 'icon' => 'sparkles', 'active_routes' => ['admin.commercial.reports.conversion.*']],
                        ['label' => 'Export History', 'description' => 'Queued commercial report exports and downloads.', 'route' => 'admin.commercial.reports.exports.index', 'permission' => 'commercial.reports.exports.view', 'icon' => 'arrow-down-tray', 'active_routes' => ['admin.commercial.reports.exports.*']],
                    ],
                ],
            ],
        ],

    ],

];
