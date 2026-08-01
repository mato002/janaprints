<?php

/**
 * Commercial workspace hub and section catalogs (presentation only).
 * Hub: Sales, Customer Service, Point Of Sale, CRM.
 * Analytics live under Reports & Intelligence.
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
            'active_routes' => [
                'admin.workspaces.commercial.section:sales',
                'admin.quotations.*',
                'admin.artwork.*',
                'admin.sales-orders.*',
                'admin.sales.desk',
                'admin.sales.desk.*',
                'admin.commercial.approvals.*',
                'admin.commercial.price-books.*',
            ],
        ],
        [
            'label' => 'Customer Service',
            'description' => 'Complaints, support tickets, statements, and inbound requests.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'customer-service'],
            'permission' => 'public_leads.quote_requests.view|public_leads.contact_messages.view|commercial.complaints.view|commercial.tickets.view|receivables.statement.view',
            'icon' => 'inbox',
            'active_routes' => [
                'admin.workspaces.commercial.section:customer-service',
                'admin.receivables.statement',
                'admin.public-quote-requests.*',
                'admin.public-contact-messages.*',
                'admin.commercial.complaints.*',
                'admin.commercial.support-tickets.*',
            ],
        ],
        [
            'label' => 'Point Of Sale',
            'description' => 'Counter sales, returns, POS sessions, and cash reconciliation.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'point-of-sale'],
            'permission' => 'pos.view|commercial.pos.sessions.view',
            'icon' => 'cash',
            'active_routes' => [
                'admin.workspaces.commercial.section:point-of-sale',
                'admin.commercial.pos.dashboard',
                'admin.commercial.pos.counter-sales',
                'admin.commercial.pos.index',
                'admin.commercial.pos.show',
                'admin.commercial.pos.holds',
                'admin.commercial.pos.resume',
                'admin.commercial.pos.pay',
                'admin.commercial.pos.sessions.*',
                'admin.commercial.pos.returns.*',
                'admin.commercial.pos.reconciliation.*',
                'admin.commercial.pos.certification.*',
            ],
        ],
        [
            'label' => 'CRM',
            'description' => 'Customers, leads, segments, and commercial activities.',
            'route' => 'admin.workspaces.commercial.section',
            'route_params' => ['section' => 'crm'],
            'permission' => 'crm.customers.view|crm.leads.view|commercial.activities.view',
            'icon' => 'user-circle',
            'active_routes' => [
                'admin.workspaces.commercial.section:crm',
                'admin.crm.customers.*',
                'admin.crm.leads.*',
                'admin.crm.segments.*',
                'admin.commercial.activities.*',
            ],
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
            ],
            'groups' => [
                [
                    'label' => 'CRM',
                    'items' => [
                        [
                            'key' => 'crm-desk',
                            'label' => 'CRM Desk',
                            'description' => 'Customers, leads, activities, and segments in one register.',
                            'route' => 'admin.crm.customers.index',
                            'permission' => 'crm.customers.view|crm.leads.view|commercial.activities.view',
                            'icon' => 'user-circle',
                            'active_routes' => [
                                'admin.crm.customers.*',
                                'admin.crm.leads.*',
                                'admin.commercial.activities.*',
                                'admin.crm.segments.*',
                            ],
                            'toolbar_actions' => [
                                [
                                    'label' => 'Create customer',
                                    'route' => 'admin.crm.customers.create',
                                    'route_params' => ['from' => 'commercial'],
                                    'permission' => 'crm.customers.create',
                                    'modal' => true,
                                ],
                            ],
                            'modes' => [
                                [
                                    'key' => 'customers',
                                    'label' => 'Customers',
                                    'route' => 'admin.crm.customers.index',
                                    'permission' => 'crm.customers.view',
                                    'active_routes' => ['admin.crm.customers.*'],
                                ],
                                [
                                    'key' => 'leads',
                                    'label' => 'Leads',
                                    'route' => 'admin.crm.leads.index',
                                    'permission' => 'crm.leads.view',
                                    'active_routes' => ['admin.crm.leads.*'],
                                ],
                                [
                                    'key' => 'activities',
                                    'label' => 'Activities',
                                    'route' => 'admin.commercial.activities.index',
                                    'permission' => 'commercial.activities.view',
                                    'active_routes' => ['admin.commercial.activities.*'],
                                ],
                                [
                                    'key' => 'segments',
                                    'label' => 'Segments',
                                    'route' => 'admin.crm.segments.index',
                                    'permission' => 'crm.customers.view',
                                    'active_routes' => ['admin.crm.segments.*'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'sales' => [
            'title' => 'Sales',
            'description' => 'Quotations, artwork, orders, pricing, and approval workflows.',
            'icon' => 'document-text',
            'quick_actions' => [
                ['label' => 'Open Sales Desk', 'route' => 'admin.workspaces.commercial.section', 'route_params' => ['section' => 'sales', 'tab' => 'sales-desk'], 'permission' => 'quotations.view|artwork.view|sales_orders.view|commercial.approvals.view|crm.customers.create|sales_orders.create'],
                ['label' => 'Create Quotation', 'route' => 'admin.quotations.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'quotations.create'],
                ['label' => 'Create Direct Order', 'route' => 'admin.sales-orders.create', 'route_params' => ['tab' => 'direct'], 'permission' => 'sales_orders.create'],
                ['label' => 'Create Artwork Request', 'route' => 'admin.artwork.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'artwork.create'],
                ['label' => 'Open Price Books', 'route' => 'admin.commercial.price-books.index', 'permission' => 'commercial.price_books.view'],
            ],
            'sales_note' => 'Use quotations for new customer work, or direct orders with print specifications for repeat production.',
            'groups' => [
                [
                    'label' => 'Sales',
                    'items' => [
                        [
                            'key' => 'sales-desk',
                            'label' => 'Sales Desk',
                            'description' => 'Quotations, artwork, sales orders, and approvals in one desk.',
                            'route' => 'admin.workspaces.commercial.section',
                            'route_params' => ['section' => 'sales', 'tab' => 'sales-desk'],
                            'permission' => 'quotations.view|artwork.view|sales_orders.view|commercial.approvals.view|crm.customers.create|sales_orders.create',
                            'icon' => 'shopping-cart',
                            'active_routes' => [
                                'admin.sales.desk',
                                'admin.sales.desk.*',
                                'admin.quotations.*',
                                'admin.artwork.*',
                                'admin.sales-orders.*',
                                'admin.commercial.approvals.*',
                            ],
                            'modes' => [
                                [
                                    'key' => 'desk',
                                    'label' => 'Walk-in',
                                    'route' => 'admin.sales.desk',
                                    'active_routes' => ['admin.sales.desk', 'admin.sales.desk.*'],
                                ],
                                [
                                    'key' => 'quotes',
                                    'label' => 'Quotes',
                                    'route' => 'admin.sales.desk',
                                    'route_params' => ['view' => 'quotes'],
                                    'permission' => 'quotations.view',
                                    'active_routes' => ['admin.quotations.*'],
                                ],
                                [
                                    'key' => 'orders',
                                    'label' => 'Orders',
                                    'route' => 'admin.sales.desk',
                                    'route_params' => ['view' => 'orders'],
                                    'permission' => 'sales_orders.view',
                                    'active_routes' => ['admin.sales-orders.*'],
                                ],
                                [
                                    'key' => 'artwork',
                                    'label' => 'Artwork',
                                    'route' => 'admin.sales.desk',
                                    'route_params' => ['view' => 'artwork'],
                                    'permission' => 'artwork.view',
                                    'active_routes' => ['admin.artwork.*'],
                                ],
                                [
                                    'key' => 'approvals',
                                    'label' => 'Approvals',
                                    'route' => 'admin.sales.desk',
                                    'route_params' => ['view' => 'approvals'],
                                    'permission' => 'commercial.approvals.view',
                                    'active_routes' => ['admin.commercial.approvals.*'],
                                ],
                            ],
                        ],
                        [
                            'key' => 'designer-desk',
                            'label' => 'Designer Desk',
                            'description' => 'Artwork queue, uploads, and designer workflow.',
                            'route' => 'admin.workspaces.commercial.section',
                            'route_params' => ['section' => 'sales', 'tab' => 'designer-desk'],
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
                                    'key' => 'desk',
                                    'label' => 'Desk',
                                    'route' => 'admin.artwork.desk',
                                    'active_routes' => [
                                        'admin.artwork.desk',
                                        'admin.artwork.desk.*',
                                        'admin.artwork.show',
                                        'admin.artwork.edit',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'Setup',
                    'items' => [
                        ['label' => 'Price Books', 'description' => 'Commercial price lists and customer pricing tiers.', 'route' => 'admin.commercial.price-books.index', 'permission' => 'commercial.price_books.view', 'icon' => 'tag', 'active_routes' => ['admin.commercial.price-books.*']],
                    ],
                ],
            ],
        ],

        'customer-service' => [
            'title' => 'Customer Service',
            'description' => 'Complaints, support cases, statements, and inbound requests.',
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
                        ['label' => 'Customer Statements', 'description' => 'Period statements of account.', 'route' => 'admin.receivables.statement', 'permission' => 'receivables.statement.view', 'icon' => 'document-text', 'active_routes' => ['admin.receivables.statement']],
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
            ],
            'groups' => [
                [
                    'label' => 'Point Of Sale',
                    'items' => [
                        [
                            'key' => 'pos-desk',
                            'label' => 'POS Desk',
                            'description' => 'Counter sales, register, sessions, returns, and cash reconciliation.',
                            'route' => 'admin.commercial.pos.counter-sales',
                            'permission' => 'pos.view|pos.counter_sales.view|commercial.pos.sessions.view|commercial.pos.returns.view|commercial.pos.reconciliation.view',
                            'icon' => 'cash',
                            'active_routes' => [
                                'admin.commercial.pos.dashboard',
                                'admin.commercial.pos.counter-sales',
                                'admin.commercial.pos.index',
                                'admin.commercial.pos.show',
                                'admin.commercial.pos.holds',
                                'admin.commercial.pos.resume',
                                'admin.commercial.pos.pay',
                                'admin.commercial.pos.sessions.*',
                                'admin.commercial.pos.returns.*',
                                'admin.commercial.pos.reconciliation.*',
                            ],
                            'modes' => [
                                [
                                    'key' => 'counter',
                                    'label' => 'Counter',
                                    'route' => 'admin.commercial.pos.counter-sales',
                                    'active_routes' => ['admin.commercial.pos.counter-sales', 'admin.commercial.pos.dashboard'],
                                ],
                                [
                                    'key' => 'sales',
                                    'label' => 'Sales',
                                    'route' => 'admin.commercial.pos.index',
                                    'active_routes' => ['admin.commercial.pos.index', 'admin.commercial.pos.show', 'admin.commercial.pos.holds', 'admin.commercial.pos.resume', 'admin.commercial.pos.pay'],
                                ],
                                [
                                    'key' => 'sessions',
                                    'label' => 'Sessions',
                                    'route' => 'admin.commercial.pos.sessions.index',
                                    'permission' => 'commercial.pos.sessions.view',
                                    'active_routes' => ['admin.commercial.pos.sessions.*'],
                                ],
                                [
                                    'key' => 'returns',
                                    'label' => 'Returns',
                                    'route' => 'admin.commercial.pos.returns.dashboard',
                                    'permission' => 'commercial.pos.returns.view',
                                    'active_routes' => ['admin.commercial.pos.returns.*'],
                                ],
                                [
                                    'key' => 'recon',
                                    'label' => 'Cash recon',
                                    'route' => 'admin.commercial.pos.reconciliation.index',
                                    'permission' => 'commercial.pos.reconciliation.view',
                                    'active_routes' => ['admin.commercial.pos.reconciliation.*'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'Setup',
                    'items' => [
                        ['label' => 'POS Certification', 'description' => 'Operational certification for inventory, accounting, cash, returns, and session truth.', 'route' => 'admin.commercial.pos.certification.index', 'permission' => 'commercial.pos.certification.view', 'icon' => 'shield-check', 'active_routes' => ['admin.commercial.pos.certification.*']],
                    ],
                ],
            ],
        ],

    ],

];
