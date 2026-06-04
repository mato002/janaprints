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
            ['label' => 'Customer', 'route' => 'admin.crm.customers.create', 'permission' => 'crm.customers.create'],
            ['label' => 'Lead', 'route' => 'admin.crm.leads.create', 'permission' => 'crm.leads.create'],
            ['label' => 'Quote', 'route' => 'admin.quotations.create', 'permission' => 'quotations.create'],
            ['label' => 'Artwork', 'route' => 'admin.artwork.create', 'permission' => 'artwork.create'],
            ['label' => 'Sales Order', 'route' => 'admin.sales-orders.create', 'permission' => 'sales_orders.create'],
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
                    ['label' => 'Scheduling', 'description' => 'Plan jobs across work centers and shifts.', 'coming_soon' => true, 'icon' => 'calendar'],
                    ['label' => 'Production Queue', 'description' => 'Queued operations awaiting execution.', 'coming_soon' => true, 'icon' => 'switch-horizontal'],
                    ['label' => 'Work Centers', 'description' => 'Machines, cells, and capacity definitions.', 'route' => 'admin.production.work-centers.index', 'permission' => 'production.view', 'icon' => 'chip', 'active_routes' => ['admin.production.work-centers.*']],
                    ['label' => 'Quality Control', 'description' => 'Inspections, holds, and QC sign-off.', 'coming_soon' => true, 'icon' => 'badge-check'],
                    ['label' => 'Dispatch', 'description' => 'Ready jobs and outbound delivery.', 'coming_soon' => true, 'icon' => 'truck'],
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
                    ['label' => 'SMS', 'description' => 'Bulk and transactional SMS.', 'coming_soon' => true, 'icon' => 'inbox'],
                    ['label' => 'WhatsApp', 'description' => 'WhatsApp Business messaging.', 'coming_soon' => true, 'icon' => 'inbox'],
                    ['label' => 'Email', 'description' => 'Email delivery and templates.', 'coming_soon' => true, 'icon' => 'inbox'],
                    ['label' => 'Campaigns', 'description' => 'Marketing and outreach campaigns.', 'coming_soon' => true, 'icon' => 'sparkles'],
                    ['label' => 'Templates', 'description' => 'Reusable message templates.', 'coming_soon' => true, 'icon' => 'document-text'],
                    ['label' => 'Notifications', 'description' => 'In-app and push notification rules.', 'coming_soon' => true, 'icon' => 'bell'],
                    ['label' => 'Communication Logs', 'description' => 'Sent message history and delivery status.', 'coming_soon' => true, 'icon' => 'clock'],
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
                    ['label' => 'Executive Dashboard', 'description' => 'Cross-module executive summary.', 'coming_soon' => true, 'icon' => 'chart-pie'],
                    ['label' => 'Commercial Reports', 'description' => 'Sales, CRM, and quotation analytics.', 'coming_soon' => true, 'icon' => 'document-text'],
                    ['label' => 'Production Reports', 'description' => 'Throughput, downtime, and job metrics.', 'coming_soon' => true, 'icon' => 'cog'],
                    ['label' => 'Inventory Reports', 'description' => 'Stock movement and valuation reports.', 'coming_soon' => true, 'icon' => 'cube'],
                    ['label' => 'Procurement Reports', 'description' => 'Purchasing and supplier performance.', 'coming_soon' => true, 'icon' => 'truck'],
                    ['label' => 'Accounting Reports', 'description' => 'Financial and management reports.', 'coming_soon' => true, 'icon' => 'currency-dollar'],
                    ['label' => 'HR Reports', 'description' => 'Workforce and payroll analytics.', 'coming_soon' => true, 'icon' => 'identification'],
                    ['label' => 'KPI Center', 'description' => 'Configurable KPI scorecards.', 'coming_soon' => true, 'icon' => 'badge-check'],
                ],
            ],
        ],
    ],

    'administration' => [
        'title' => 'Administration',
        'description' => 'Access control, organization structure, settings, and audit.',
        'icon' => 'shield-check',
        'quick_create' => [
            ['label' => 'User', 'route' => 'admin.users.create', 'permission' => 'users.create'],
            ['label' => 'Role', 'route' => 'admin.roles.create', 'permission' => 'roles.create'],
            ['label' => 'Branch', 'route' => 'admin.branches.create', 'permission' => 'branches.manage'],
            ['label' => 'Department', 'route' => 'admin.departments.create', 'permission' => 'departments.manage'],
        ],
        'groups' => [
            [
                'label' => 'Access Control',
                'items' => [
                    ['label' => 'Users', 'description' => 'User accounts, branches, and role assignment.', 'route' => 'admin.users.index', 'permission' => 'users.view', 'icon' => 'users', 'active_routes' => ['admin.users.*']],
                    ['label' => 'Roles', 'description' => 'Security groups and role governance.', 'route' => 'admin.access-control.roles', 'permission' => 'roles.view', 'icon' => 'shield-check', 'active_routes' => ['admin.access-control.roles', 'admin.roles.*']],
                    ['label' => 'Permissions', 'description' => 'Permission matrix and access rights.', 'route' => 'admin.access-control.matrix', 'permission' => 'roles.view', 'icon' => 'key', 'active_routes' => ['admin.access-control.matrix', 'admin.permissions.*', 'admin.roles.permissions.*']],
                ],
            ],
            [
                'label' => 'Organization',
                'items' => [
                    ['label' => 'Companies', 'description' => 'Legal entities and tenant companies.', 'route' => 'admin.companies.index', 'permission' => 'companies.manage', 'icon' => 'building', 'active_routes' => ['admin.companies.*']],
                    ['label' => 'Branches', 'description' => 'Branch locations and defaults.', 'route' => 'admin.branches.index', 'permission' => 'branches.manage', 'icon' => 'location-marker', 'active_routes' => ['admin.branches.*']],
                    ['label' => 'Departments', 'description' => 'Organizational units and hierarchy.', 'route' => 'admin.departments.index', 'permission' => 'departments.manage', 'icon' => 'view-grid', 'active_routes' => ['admin.departments.*']],
                    ['label' => 'Employees', 'description' => 'Employee master records.', 'route' => 'admin.employees.index', 'permission' => 'employees.manage', 'icon' => 'identification', 'active_routes' => ['admin.employees.*']],
                ],
            ],
            [
                'label' => 'Settings Hub',
                'items' => [
                    ['label' => 'System Settings', 'description' => 'Company-wide configuration and preferences.', 'route' => 'admin.settings.index', 'permission' => 'settings.view', 'icon' => 'cog', 'active_routes' => ['admin.settings.index', 'admin.settings.show', 'admin.settings.update', 'admin.settings.branding.*']],
                    ['label' => 'Form Controls', 'description' => 'Required fields and form visibility rules.', 'route' => 'admin.settings.forms.index', 'permission' => 'settings.view', 'icon' => 'clipboard-list', 'active_routes' => ['admin.settings.forms.*']],
                    ['label' => 'Approval Rules', 'description' => 'Discount, credit, and workflow approvals.', 'route' => 'admin.settings.approvals.index', 'permission' => 'settings.view', 'icon' => 'badge-check', 'active_routes' => ['admin.settings.approvals.*']],
                    ['label' => 'Numbering Rules', 'description' => 'Document sequences and prefixes.', 'route' => 'admin.settings.numbering.index', 'permission' => 'settings.view', 'icon' => 'template', 'active_routes' => ['admin.settings.numbering.*']],
                    ['label' => 'Notifications', 'description' => 'System notification preferences.', 'coming_soon' => true, 'icon' => 'bell'],
                    ['label' => 'Integrations', 'description' => 'Third-party connectors and webhooks.', 'coming_soon' => true, 'icon' => 'switch-horizontal'],
                    ['label' => 'API', 'description' => 'API keys and developer access.', 'coming_soon' => true, 'icon' => 'key'],
                ],
            ],
            [
                'label' => 'Audit Center',
                'items' => [
                    ['label' => 'Activity Logs', 'description' => 'User actions and system activity trail.', 'route' => 'admin.activity-logs.index', 'permission' => 'activity_logs.view', 'icon' => 'clock', 'active_routes' => ['admin.activity-logs.*']],
                    ['label' => 'Audit Logs', 'description' => 'Compliance-grade audit records.', 'coming_soon' => true, 'icon' => 'document-text'],
                    ['label' => 'Login Logs', 'description' => 'Authentication and session history.', 'coming_soon' => true, 'icon' => 'key'],
                    ['label' => 'System Events', 'description' => 'Infrastructure and integration events.', 'coming_soon' => true, 'icon' => 'chip'],
                ],
            ],
        ],
    ],

];
