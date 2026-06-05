<?php

/**
 * Workspace hub feature catalog (presentation only).
 * Sidebar shows workspaces; features live on workspace hub pages.
 */
return [

    'commercial' => [
        'title' => 'Commercial',
        'description' => 'CRM, sales, customer service, point of sale, and commercial reporting.',
        'icon' => 'shopping-cart',
        'managed_by' => 'commercial_workspaces',
        'quick_create' => [
            ['label' => 'Customer', 'route' => 'admin.crm.customers.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'crm.customers.create'],
            ['label' => 'Lead', 'route' => 'admin.crm.leads.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'crm.leads.create'],
            ['label' => 'Quotation', 'route' => 'admin.quotations.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'quotations.create'],
            ['label' => 'Artwork Request', 'route' => 'admin.artwork.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'artwork.create'],
            ['label' => 'Activity', 'route' => 'admin.commercial.activities.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'crm.activities.create'],
            ['label' => 'Complaint', 'route' => 'admin.commercial.complaints.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'commercial.complaints.create'],
            ['label' => 'Support Ticket', 'route' => 'admin.commercial.support-tickets.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'commercial.tickets.create'],
            ['label' => 'POS Sale', 'route' => 'admin.commercial.pos.create', 'route_params' => ['from' => 'commercial'], 'permission' => 'pos.create|pos.counter_sales.create'],
        ],
        'groups' => [],
    ],

    'production' => [
        'title' => 'Production',
        'description' => 'Job cards, scheduling, work centers, quality, and dispatch.',
        'icon' => 'cog',
        'quick_create' => [
            ['label' => 'Job Card', 'route' => 'admin.production.job-cards.create', 'permission' => 'production.create'],
        ],
        'groups' => [
            [
                'label' => 'Operations',
                'items' => [
                    ['label' => 'Production Dashboard', 'description' => 'Live production KPIs and workload overview.', 'route' => 'admin.production.dashboard', 'permission' => 'production.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.production.dashboard']],
                    ['label' => 'Job Cards', 'description' => 'Production jobs from order to completion.', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'icon' => 'collection', 'active_routes' => ['admin.production.job-cards.*']],
                    ['label' => 'Job Costing & Profitability', 'description' => 'Job cost sheets and margin analysis.', 'route' => 'admin.production.costing.dashboard', 'permission' => 'production.costing.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.production.costing.*', 'admin.production.job-cards.costing']],
                    ['label' => 'Scheduling', 'description' => 'Plan jobs across work centers and shifts.', 'route' => 'admin.production.scheduling.index', 'permission' => 'production.scheduling.view', 'icon' => 'calendar', 'active_routes' => ['admin.production.scheduling.*']],
                    ['label' => 'Production Queue', 'description' => 'Queued operations awaiting execution.', 'route' => 'admin.production.queue.index', 'permission' => 'production.queue.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.production.queue.*']],
                    ['label' => 'Work Centers', 'description' => 'Machines, cells, and capacity definitions.', 'route' => 'admin.production.work-centers.index', 'permission' => 'production.work-centers.view', 'icon' => 'chip', 'active_routes' => ['admin.production.work-centers.*']],
                    ['label' => 'Quality Control', 'description' => 'Inspections, holds, and QC sign-off.', 'route' => 'admin.production.quality.index', 'permission' => 'production.quality.view', 'icon' => 'badge-check', 'active_routes' => ['admin.production.quality.*']],
                    ['label' => 'Dispatch (legacy)', 'description' => 'Use Dispatch workspace for delivery notes.', 'route' => 'admin.workspaces.dispatch', 'permission' => 'dispatch.view', 'icon' => 'truck', 'active_routes' => ['admin.workspaces.dispatch']],
                ],
            ],
        ],
    ],

    'dispatch' => [
        'title' => 'Dispatch',
        'description' => 'Delivery notes, dispatch lifecycle, and outbound delivery truth.',
        'icon' => 'truck',
        'quick_create' => [],
        'groups' => [
            [
                'label' => 'Outbound',
                'items' => [
                    ['label' => 'Dispatch Dashboard', 'description' => 'Ready jobs, dispatch and delivery metrics.', 'route' => 'admin.dispatch.dashboard', 'permission' => 'dispatch.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.dispatch.dashboard']],
                    ['label' => 'Delivery Notes', 'description' => 'Create, dispatch, and confirm deliveries.', 'route' => 'admin.dispatch.delivery-notes.index', 'permission' => 'dispatch.view', 'icon' => 'document-text', 'active_routes' => ['admin.dispatch.delivery-notes.*']],
                    ['label' => 'Delivery Calendar', 'description' => 'Scheduled deliveries calendar.', 'route' => 'admin.dispatch.calendar', 'permission' => 'dispatch.view', 'icon' => 'calendar', 'active_routes' => ['admin.dispatch.calendar']],
                ],
            ],
        ],
    ],

    'supply-chain' => [
        'title' => 'Supply Chain',
        'description' => 'Catalogue, store operations, procurement, inventory control, costing, assets, and reports.',
        'icon' => 'cube',
        'managed_by' => 'supply_chain_workspaces',
        'quick_create' => [
            ['label' => 'Item', 'route' => 'admin.inventory.items.create', 'permission' => 'catalogue.create'],
            ['label' => 'Brand', 'route' => 'admin.inventory.catalogue.brands.create', 'permission' => 'catalogue.create'],
            ['label' => 'Receipt', 'route' => 'admin.inventory.receipts.create', 'permission' => 'inventory.receive'],
            ['label' => 'New Stock Issue', 'route' => 'admin.inventory.issues.create', 'permission' => 'inventory.issue'],
            ['label' => 'Purchase Request', 'route' => 'admin.procurement.requests.create', 'permission' => 'procurement.requests.create'],
        ],
        'groups' => [],
    ],

    'accounting' => [
        'title' => 'Accounting',
        'description' => 'Finance command center organized into ledger, receivables, payables, tax, and setup workspaces.',
        'icon' => 'currency-dollar',
        'managed_by' => 'accounting_workspaces',
        'quick_create' => [
            ['label' => 'Journal', 'route' => 'admin.accounting.journals.create', 'permission' => 'accounting.journals.create'],
            ['label' => 'Invoice', 'route' => 'admin.invoices.index', 'permission' => 'invoices.view'],
            ['label' => 'Payment', 'route' => 'admin.payments.create', 'permission' => 'payments.create'],
            ['label' => 'Supplier Bill', 'route' => 'admin.payables.bills.create', 'permission' => 'payables.bills.create'],
        ],
        'groups' => [],
    ],

    'hr' => [
        'title' => 'HR',
        'description' => 'Employees, attendance, leave, payroll, and HR records.',
        'icon' => 'identification',
        'quick_create' => [
            ['label' => 'Employee', 'route' => 'admin.employees.create', 'permission' => 'employees.manage'],
            ['label' => 'Leave Request', 'coming_soon' => true],
            ['label' => 'Payroll Run', 'coming_soon' => true],
        ],
        'groups' => [
            [
                'label' => 'People',
                'items' => [
                    ['label' => 'HR Dashboard', 'description' => 'Workforce metrics and HR overview.', 'coming_soon' => true, 'icon' => 'chart-pie'],
                    ['label' => 'Employees', 'description' => 'Employee records linked to user accounts.', 'route' => 'admin.employees.index', 'permission' => 'employees.manage', 'icon' => 'identification', 'active_routes' => ['admin.employees.*']],
                    ['label' => 'Attendance', 'description' => 'Time tracking and shift records.', 'coming_soon' => true, 'icon' => 'clock'],
                    ['label' => 'Leave', 'description' => 'Leave requests and balances.', 'coming_soon' => true, 'icon' => 'calendar'],
                    ['label' => 'Payroll', 'description' => 'Pay runs, payslips, and deductions.', 'coming_soon' => true, 'icon' => 'cash'],
                    ['label' => 'Performance', 'description' => 'Reviews, goals, and appraisals.', 'coming_soon' => true, 'icon' => 'badge-check'],
                    ['label' => 'Training', 'description' => 'Courses, certifications, and development.', 'coming_soon' => true, 'icon' => 'book-open'],
                    ['label' => 'Documents', 'description' => 'HR document repository.', 'coming_soon' => true, 'icon' => 'document-text'],
                    ['label' => 'Exit Management', 'description' => 'Offboarding and clearance workflows.', 'coming_soon' => true, 'icon' => 'switch-horizontal'],
                ],
            ],
        ],
    ],

    'assets' => [
        'title' => 'Assets',
        'description' => 'Fixed assets, maintenance schedules, and depreciation.',
        'icon' => 'chip',
        'quick_create' => [],
        'groups' => [
            [
                'label' => 'Asset Register',
                'items' => [
                    ['label' => 'Machines', 'description' => 'Production and plant equipment.', 'coming_soon' => true, 'icon' => 'cog'],
                    ['label' => 'Vehicles', 'description' => 'Fleet and delivery vehicles.', 'coming_soon' => true, 'icon' => 'truck'],
                    ['label' => 'Computers', 'description' => 'IT hardware and peripherals.', 'coming_soon' => true, 'icon' => 'template'],
                    ['label' => 'Maintenance', 'description' => 'Preventive and corrective maintenance.', 'coming_soon' => true, 'icon' => 'cog'],
                    ['label' => 'Depreciation', 'description' => 'Asset depreciation schedules.', 'coming_soon' => true, 'icon' => 'chart-pie'],
                ],
            ],
        ],
    ],

    'communications' => [
        'title' => 'Communications',
        'description' => 'SMS, email, campaigns, templates, and notification logs.',
        'icon' => 'inbox',
        'quick_create' => [],
        'groups' => [
            [
                'label' => 'Channels',
                'items' => [
                    ['label' => 'SMS Dashboard', 'description' => 'Credits, usage charts, and delivery KPIs.', 'route' => 'admin.communications.sms.dashboard', 'permission' => 'communications.sms.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.communications.sms.dashboard']],
                    ['label' => 'SMS Campaigns', 'description' => 'Create, preview, schedule, and send bulk SMS.', 'route' => 'admin.communications.sms.campaigns.index', 'permission' => 'communications.sms.view', 'icon' => 'sparkles', 'active_routes' => ['admin.communications.sms.campaigns.*']],
                    ['label' => 'SMS Queue', 'description' => 'Queued, processing, sent, and failed messages.', 'route' => 'admin.communications.sms.queues.index', 'permission' => 'communications.sms.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.communications.sms.queues.*']],
                    ['label' => 'Provider Logs', 'description' => 'SMS provider request and response audit trail.', 'route' => 'admin.communications.sms.provider-logs.index', 'permission' => 'communications.sms.audit', 'icon' => 'clock', 'active_routes' => ['admin.communications.sms.provider-logs.*']],
                    ['label' => 'SMS Credit Ledger', 'description' => 'Credits, purchases, usage, and balances.', 'route' => 'admin.communications.sms.credits.index', 'permission' => 'communications.sms.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.communications.sms.credits.*']],
                    ['label' => 'WhatsApp', 'description' => 'Conversation center — inbox, templates, and delivery.', 'route' => 'admin.communications.whatsapp.inbox', 'permission' => 'communications.whatsapp.view', 'icon' => 'inbox', 'active_routes' => ['admin.communications.whatsapp.*']],
                    ['label' => 'Email Center', 'description' => 'Compose, campaigns, delivery tracking, and analytics.', 'route' => 'admin.communications.email.dashboard', 'permission' => 'communications.email.view', 'icon' => 'mail', 'active_routes' => ['admin.communications.email.*']],
                    ['label' => 'Templates', 'description' => 'Reusable message templates with versioning and preview.', 'route' => 'admin.communications.templates.index', 'permission' => 'communications.templates.view', 'icon' => 'document-text', 'active_routes' => ['admin.communications.templates.*']],
                    ['label' => 'Notification Center', 'description' => 'Internal ERP alerts, preferences, and notification history.', 'route' => 'admin.communications.notifications.index', 'permission' => 'communications.notifications.view', 'icon' => 'bell', 'active_routes' => ['admin.communications.notifications.*']],
                    ['label' => 'Communication Logs', 'description' => 'Communication truth ledger — all channels in one timeline.', 'route' => 'admin.communications.logs.dashboard', 'permission' => 'communications.logs.view', 'icon' => 'clock', 'active_routes' => ['admin.communications.logs.*']],
                    ['label' => 'Shared Inbox', 'description' => 'WhatsApp-style team inbox — threads, notes, handover, and CEO view.', 'route' => 'admin.communications.inbox.index', 'permission' => 'communications.inbox.view', 'icon' => 'inbox', 'active_routes' => ['admin.communications.inbox.*']],
                ],
            ],
        ],
    ],

    'reports' => [
        'title' => 'Reports & Intelligence',
        'description' => 'Executive dashboards, module reports, and KPI center.',
        'icon' => 'chart-pie',
        'quick_create' => [],
        'groups' => [
            [
                'label' => 'Reporting',
                'items' => [
                    ['label' => 'Executive Dashboard', 'description' => 'Cross-module executive summary.', 'route' => 'admin.reports.executive', 'permission' => 'reports.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.reports.executive']],
                    ['label' => 'Commercial Reports', 'description' => 'Hub for departmental commercial reports and Commercial 360.', 'route' => 'admin.reports.commercial', 'permission' => 'reports.view', 'icon' => 'document-text', 'active_routes' => ['admin.reports.commercial', 'commercial.reports.*', 'admin.reports.commercial360']],
                    ['label' => 'Production Reports', 'description' => 'Throughput, downtime, and job metrics.', 'route' => 'admin.reports.production', 'permission' => 'reports.view', 'icon' => 'cog', 'active_routes' => ['admin.reports.production']],
                    ['label' => 'Inventory Reports', 'description' => 'Stock movement and valuation reports.', 'route' => 'admin.reports.inventory', 'permission' => 'reports.view', 'icon' => 'cube', 'active_routes' => ['admin.reports.inventory']],
                    ['label' => 'Procurement Reports', 'description' => 'Purchasing and supplier performance.', 'route' => 'admin.reports.procurement', 'permission' => 'reports.view', 'icon' => 'truck', 'active_routes' => ['admin.reports.procurement']],
                    ['label' => 'Accounting Reports', 'description' => 'Financial and management reports.', 'route' => 'admin.reports.accounting', 'permission' => 'reports.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.reports.accounting']],
                    ['label' => 'HR Reports', 'description' => 'Workforce and payroll analytics.', 'route' => 'admin.reports.hr', 'permission' => 'reports.view', 'icon' => 'identification', 'active_routes' => ['admin.reports.hr']],
                    ['label' => 'KPI Center', 'description' => 'Configurable KPI scorecards.', 'route' => 'admin.reports.kpi', 'permission' => 'kpi.view|reports.view', 'icon' => 'badge-check', 'active_routes' => ['admin.reports.kpi']],
                ],
            ],
            [
                'label' => '360 Intelligence',
                'items' => [
                    ['label' => 'Commercial 360', 'description' => 'Sales and customer management intelligence.', 'route' => 'admin.reports.commercial360', 'permission' => 'intelligence.commercial.view|reports.view', 'icon' => 'document-text', 'active_routes' => ['admin.reports.commercial360']],
                    ['label' => 'Production 360', 'description' => 'Production pipeline and delay intelligence.', 'route' => 'admin.reports.production360', 'permission' => 'intelligence.production.view|reports.view', 'icon' => 'cog', 'active_routes' => ['admin.reports.production360']],
                    ['label' => 'Inventory 360', 'description' => 'Stock health, valuation, and movement intelligence.', 'route' => 'admin.reports.inventory360', 'permission' => 'intelligence.inventory.view|reports.view', 'icon' => 'cube', 'active_routes' => ['admin.reports.inventory360']],
                    ['label' => 'Procurement 360', 'description' => 'Purchasing and vendor performance intelligence.', 'route' => 'admin.reports.procurement360', 'permission' => 'intelligence.vendor.view|reports.view', 'icon' => 'truck', 'active_routes' => ['admin.reports.procurement360']],
                    ['label' => 'Financial 360', 'description' => 'Revenue, receivables, and payables intelligence.', 'route' => 'admin.reports.financial360', 'permission' => 'intelligence.financial.view|reports.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.reports.financial360']],
                    ['label' => 'Branch 360', 'description' => 'Branch comparison and performance profiles.', 'route' => 'admin.reports.branch360', 'permission' => 'intelligence.branch.view|reports.view', 'icon' => 'office-building', 'active_routes' => ['admin.reports.branch360']],
                ],
            ],
        ],
    ],

    'administration' => [
        'title' => 'Administration',
        'description' => 'Access control, organization structure, settings, and audit.',
        'icon' => 'shield-check',
        'managed_by' => 'administration_workspaces',
        'quick_create' => [
            ['label' => 'User', 'route' => 'admin.users.create', 'permission' => 'users.create'],
            ['label' => 'Role', 'route' => 'admin.roles.create', 'permission' => 'roles.create'],
            ['label' => 'Branch', 'route' => 'admin.branches.create', 'permission' => 'branches.manage'],
            ['label' => 'Department', 'route' => 'admin.departments.create', 'permission' => 'departments.manage'],
        ],
        'groups' => [],
    ],

];
