<?php

/**
 * UI-only permission catalog for Access Control screens.
 * Maps existing permission keys to business modules — does not alter authorization.
 */

return [

    'columns' => [
        'view' => ['label' => 'View'],
        'create' => ['label' => 'Create'],
        'edit' => ['label' => 'Edit'],
        'delete' => ['label' => 'Delete'],
        'approve' => ['label' => 'Approve'],
    ],

    'modules' => [

        'administration' => [
            'label' => 'Administration',
            'entities' => [
                'users' => [
                    'label' => 'Users',
                    'permissions' => [
                        'view' => 'users.view',
                        'create' => 'users.create',
                        'edit' => 'users.edit',
                        'delete' => 'users.delete',
                    ],
                ],
                'roles' => [
                    'label' => 'Roles',
                    'permissions' => [
                        'view' => 'roles.view',
                        'create' => 'roles.create',
                        'edit' => 'roles.edit',
                        'delete' => 'roles.delete',
                    ],
                ],
                'activity_logs' => [
                    'label' => 'Activity Logs',
                    'permissions' => [
                        'view' => 'activity_logs.view',
                    ],
                ],
                'settings' => [
                    'label' => 'Settings',
                    'permissions' => [
                        'view' => 'settings.view',
                        'edit' => 'settings.manage',
                    ],
                ],
            ],
        ],

        'organization' => [
            'label' => 'Organization',
            'entities' => [
                'companies' => [
                    'label' => 'Companies',
                    'permissions' => [
                        'edit' => 'companies.manage',
                    ],
                ],
                'branches' => [
                    'label' => 'Branches',
                    'permissions' => [
                        'edit' => 'branches.manage',
                    ],
                ],
                'departments' => [
                    'label' => 'Departments',
                    'permissions' => [
                        'edit' => 'departments.manage',
                    ],
                ],
                'employees' => [
                    'label' => 'Employees',
                    'permissions' => [
                        'edit' => 'employees.manage',
                    ],
                ],
            ],
        ],

        'crm' => [
            'label' => 'CRM',
            'entities' => [
                'customers' => [
                    'label' => 'Customers',
                    'permissions' => [
                        'view' => 'crm.customers.view',
                        'create' => 'crm.customers.create',
                        'edit' => 'crm.customers.edit',
                        'delete' => 'crm.customers.delete',
                    ],
                ],
                'leads' => [
                    'label' => 'Leads',
                    'permissions' => [
                        'view' => 'crm.leads.view',
                        'create' => 'crm.leads.create',
                        'edit' => 'crm.leads.edit',
                        'delete' => 'crm.leads.delete',
                    ],
                ],
                'activities' => [
                    'label' => 'Activities',
                    'permissions' => [
                        'view' => 'crm.activities.view',
                        'create' => 'crm.activities.create',
                        'edit' => 'crm.activities.edit',
                        'delete' => 'crm.activities.delete',
                    ],
                ],
            ],
        ],

        'sales' => [
            'label' => 'Sales',
            'entities' => [
                'quotations' => [
                    'label' => 'Quotations',
                    'permissions' => [
                        'view' => 'quotations.view',
                        'create' => 'quotations.create',
                        'edit' => 'quotations.edit',
                        'delete' => 'quotations.delete',
                        'approve' => 'quotations.approve',
                    ],
                    'extra' => [
                        ['label' => 'Send', 'permission' => 'quotations.send'],
                        ['label' => 'Convert', 'permission' => 'quotations.convert'],
                    ],
                ],
                'sales_orders' => [
                    'label' => 'Sales Orders',
                    'permissions' => [
                        'view' => 'sales_orders.view',
                        'create' => 'sales_orders.create',
                        'edit' => 'sales_orders.edit',
                        'delete' => 'sales_orders.delete',
                        'approve' => 'sales_orders.confirm',
                    ],
                    'extra' => [
                        ['label' => 'Production handoff', 'permission' => 'sales_orders.production'],
                        ['label' => 'Close', 'permission' => 'sales_orders.close'],
                    ],
                ],
                'pos' => [
                    'label' => 'Point of Sale',
                    'permissions' => [
                        'view' => 'pos.view',
                        'create' => 'pos.create',
                        'edit' => 'pos.edit',
                        'cancel' => 'pos.cancel',
                        'refund' => 'pos.refund',
                    ],
                    'extra' => [
                        ['label' => 'View sessions', 'permission' => 'commercial.pos.sessions.view'],
                        ['label' => 'Open sessions', 'permission' => 'commercial.pos.sessions.open'],
                        ['label' => 'Close sessions', 'permission' => 'commercial.pos.sessions.close'],
                        ['label' => 'Session audit', 'permission' => 'commercial.pos.sessions.audit'],
                        ['label' => 'Session admin', 'permission' => 'commercial.pos.sessions.admin'],
                        ['label' => 'View reconciliation', 'permission' => 'commercial.pos.reconciliation.view'],
                        ['label' => 'Submit reconciliation', 'permission' => 'commercial.pos.reconciliation.create'],
                        ['label' => 'Approve reconciliation', 'permission' => 'commercial.pos.reconciliation.approve'],
                        ['label' => 'Reconciliation audit', 'permission' => 'commercial.pos.reconciliation.audit'],
                        ['label' => 'View returns', 'permission' => 'commercial.pos.returns.view'],
                        ['label' => 'Create returns', 'permission' => 'commercial.pos.returns.create'],
                        ['label' => 'Approve returns', 'permission' => 'commercial.pos.returns.approve'],
                        ['label' => 'Return audit', 'permission' => 'commercial.pos.returns.audit'],
                        ['label' => 'View POS intelligence', 'permission' => 'commercial.pos.reports.view'],
                        ['label' => 'Export POS intelligence', 'permission' => 'commercial.pos.reports.export'],
                        ['label' => 'View POS certification', 'permission' => 'commercial.pos.certification.view'],
                        ['label' => 'Counter sales workstation', 'permission' => 'pos.counter_sales.view'],
                        ['label' => 'Counter sales create', 'permission' => 'pos.counter_sales.create'],
                        ['label' => 'Counter sales hold', 'permission' => 'pos.counter_sales.hold'],
                        ['label' => 'Counter sales complete', 'permission' => 'pos.counter_sales.complete'],
                        ['label' => 'Counter sales cancel', 'permission' => 'pos.counter_sales.cancel'],
                    ],
                ],
            ],
        ],

        'artwork' => [
            'label' => 'Artwork',
            'entities' => [
                'artwork' => [
                    'label' => 'Artwork Requests',
                    'permissions' => [
                        'view' => 'artwork.view',
                        'create' => 'artwork.create',
                        'edit' => 'artwork.edit',
                        'delete' => 'artwork.delete',
                        'approve' => 'artwork.approve',
                    ],
                    'extra' => [
                        ['label' => 'Assign', 'permission' => 'artwork.assign'],
                        ['label' => 'Submit', 'permission' => 'artwork.submit'],
                    ],
                ],
            ],
        ],

        'production' => [
            'label' => 'Production',
            'entities' => [
                'jobs' => [
                    'label' => 'Production Jobs',
                    'permissions' => [
                        'view' => 'production.view',
                        'create' => 'production.create',
                        'edit' => 'production.edit',
                        'delete' => 'production.delete',
                        'approve' => 'production.qc',
                    ],
                    'extra' => [
                        ['label' => 'Schedule', 'permission' => 'production.schedule'],
                        ['label' => 'Start', 'permission' => 'production.start'],
                        ['label' => 'Complete', 'permission' => 'production.complete'],
                        ['label' => 'Job costing', 'permission' => 'production.costing.view'],
                        ['label' => 'Manage costing', 'permission' => 'production.costing.manage'],
                    ],
                ],
            ],
        ],

        'inventory' => [
            'label' => 'Inventory',
            'entities' => [
                'catalogue' => [
                    'label' => 'Catalogue',
                    'permissions' => [
                        'view' => 'catalogue.view',
                        'create' => 'catalogue.create',
                        'edit' => 'catalogue.edit',
                        'delete' => 'catalogue.delete',
                    ],
                ],
                'stores' => [
                    'label' => 'Stores',
                    'permissions' => [
                        'view' => 'inventory.view',
                        'create' => 'inventory.create',
                        'edit' => 'inventory.edit',
                        'delete' => 'inventory.delete',
                    ],
                    'extra' => [
                        ['label' => 'Receive', 'permission' => 'inventory.receive'],
                        ['label' => 'Issue', 'permission' => 'inventory.issue'],
                        ['label' => 'Adjust', 'permission' => 'inventory.adjust'],
                        ['label' => 'Transfer', 'permission' => 'inventory.transfer'],
                    ],
                ],
                'stock' => [
                    'label' => 'Inventory',
                    'permissions' => [
                        'view' => 'inventory.view',
                        'create' => 'inventory.create',
                        'edit' => 'inventory.edit',
                        'delete' => 'inventory.delete',
                    ],
                    'extra' => [
                        ['label' => 'Receive', 'permission' => 'inventory.receive'],
                        ['label' => 'Issue', 'permission' => 'inventory.issue'],
                        ['label' => 'Adjust', 'permission' => 'inventory.adjust'],
                        ['label' => 'Transfer', 'permission' => 'inventory.transfer'],
                        ['label' => 'Valuation', 'permission' => 'inventory.valuation.view'],
                    ],
                ],
            ],
        ],

        'procurement' => [
            'label' => 'Procurement',
            'entities' => [
                'vendors' => [
                    'label' => 'Vendors',
                    'permissions' => [
                        'view' => 'procurement.vendors.view',
                        'create' => 'procurement.vendors.create',
                        'edit' => 'procurement.vendors.edit',
                        'delete' => 'procurement.vendors.delete',
                    ],
                ],
                'requests' => [
                    'label' => 'Purchase Requests',
                    'permissions' => [
                        'view' => 'procurement.requests.view',
                        'create' => 'procurement.requests.create',
                        'edit' => 'procurement.requests.edit',
                        'delete' => 'procurement.requests.delete',
                        'approve' => 'procurement.requests.approve',
                    ],
                ],
                'orders' => [
                    'label' => 'Purchase Orders',
                    'permissions' => [
                        'view' => 'procurement.orders.view',
                        'create' => 'procurement.orders.create',
                        'edit' => 'procurement.orders.edit',
                        'delete' => 'procurement.orders.delete',
                        'approve' => 'procurement.orders.approve',
                    ],
                    'extra' => [
                        ['label' => 'Receive goods', 'permission' => 'procurement.orders.receive'],
                    ],
                ],
                'rfq' => [
                    'label' => 'RFQ',
                    'permissions' => [
                        'view' => 'procurement.rfq.view',
                        'create' => 'procurement.rfq.create',
                        'edit' => 'procurement.rfq.edit',
                    ],
                ],
                'comparison' => [
                    'label' => 'Vendor Comparison',
                    'permissions' => [
                        'view' => 'procurement.comparison.view',
                    ],
                    'extra' => [
                        ['label' => 'Award vendor', 'permission' => 'procurement.comparison.manage'],
                    ],
                ],
            ],
        ],

        'assets' => [
            'label' => 'Fixed Assets',
            'entities' => [
                'register' => [
                    'label' => 'Asset Register',
                    'permissions' => [
                        'view' => 'assets.view',
                        'create' => 'assets.create',
                        'edit' => 'assets.edit',
                    ],
                    'extra' => [
                        ['label' => 'Manage lifecycle', 'permission' => 'assets.manage'],
                    ],
                ],
            ],
        ],

        'accounting' => [
            'label' => 'Accounting',
            'entities' => [
                'chart_of_accounts' => [
                    'label' => 'Chart of Accounts',
                    'permissions' => [
                        'view' => 'accounting.chart.view',
                        'create' => 'accounting.chart.create',
                        'edit' => 'accounting.chart.edit',
                        'delete' => 'accounting.chart.delete',
                    ],
                    'extra' => [
                        ['label' => 'Lock / Unlock', 'permission' => 'accounting.chart.lock'],
                    ],
                ],
                'periods' => [
                    'label' => 'Accounting Periods',
                    'permissions' => [
                        'view' => 'accounting.periods.view',
                        'create' => 'accounting.periods.create',
                        'edit' => 'accounting.periods.manage',
                        'delete' => 'accounting.periods.reopen',
                    ],
                    'extra' => [
                        ['label' => 'Close', 'permission' => 'accounting.periods.close'],
                        ['label' => 'Lock', 'permission' => 'accounting.periods.lock'],
                    ],
                ],
                'journals' => [
                    'label' => 'Journals & GL',
                    'permissions' => [
                        'view' => 'accounting.journals.view',
                        'create' => 'accounting.journals.create',
                    ],
                    'extra' => [
                        ['label' => 'Post', 'permission' => 'accounting.journals.post'],
                        ['label' => 'Reverse', 'permission' => 'accounting.journals.reverse'],
                    ],
                ],
                'posting' => [
                    'label' => 'Posting Engine',
                    'permissions' => [
                        'view' => 'accounting.posting.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage rules & templates', 'permission' => 'accounting.posting.manage'],
                        ['label' => 'View posting rules workspace', 'permission' => 'accounting.posting_rules.view'],
                        ['label' => 'Manage posting rules', 'permission' => 'accounting.posting_rules.manage'],
                        ['label' => 'Audit posting rules', 'permission' => 'accounting.posting_rules.audit'],
                    ],
                ],
                'invoices' => [
                    'label' => 'Customer Invoices',
                    'permissions' => [
                        'view' => 'invoices.view',
                        'create' => 'invoices.create',
                        'edit' => 'invoices.edit',
                        'delete' => 'invoices.delete',
                        'approve' => 'invoices.approve',
                    ],
                    'extra' => [
                        ['label' => 'Post to AR', 'permission' => 'invoices.post'],
                        ['label' => 'Cancel', 'permission' => 'invoices.cancel'],
                        ['label' => 'Credit note', 'permission' => 'invoices.credit_note'],
                    ],
                ],
                'payments' => [
                    'label' => 'Customer Payments',
                    'permissions' => [
                        'view' => 'payments.view',
                        'create' => 'payments.create',
                        'edit' => 'payments.edit',
                        'delete' => 'payments.delete',
                    ],
                    'extra' => [
                        ['label' => 'Post', 'permission' => 'payments.post'],
                        ['label' => 'Cancel', 'permission' => 'payments.cancel'],
                    ],
                ],
                'financial_reports' => [
                    'label' => 'Financial Reports',
                    'permissions' => [
                        'view' => 'accounting.reports.view',
                    ],
                ],
                'dashboard' => [
                    'label' => 'Accounting Dashboard',
                    'permissions' => [
                        'view' => 'accounting.dashboard.view',
                    ],
                ],
                'receivables' => [
                    'label' => 'Receivables Reports',
                    'permissions' => [
                        'view' => 'receivables.ledger.view',
                    ],
                    'extra' => [
                        ['label' => 'Customer statement', 'permission' => 'receivables.statement.view'],
                        ['label' => 'Aging analysis', 'permission' => 'receivables.aging.view'],
                    ],
                ],
                'payables_bills' => [
                    'label' => 'Supplier Bills',
                    'permissions' => [
                        'view' => 'payables.bills.view',
                        'create' => 'payables.bills.create',
                        'edit' => 'payables.bills.edit',
                        'delete' => 'payables.bills.delete',
                        'approve' => 'payables.bills.approve',
                    ],
                    'extra' => [
                        ['label' => 'Post to AP', 'permission' => 'payables.bills.post'],
                        ['label' => 'Cancel', 'permission' => 'payables.bills.cancel'],
                        ['label' => 'Credit note', 'permission' => 'payables.bills.credit_note'],
                    ],
                ],
                'payables_payments' => [
                    'label' => 'Supplier Payments',
                    'permissions' => [
                        'view' => 'payables.payments.view',
                        'create' => 'payables.payments.create',
                        'edit' => 'payables.payments.edit',
                        'delete' => 'payables.payments.delete',
                    ],
                    'extra' => [
                        ['label' => 'Post', 'permission' => 'payables.payments.post'],
                        ['label' => 'Cancel', 'permission' => 'payables.payments.cancel'],
                    ],
                ],
                'payables_reports' => [
                    'label' => 'Payables Reports',
                    'permissions' => [
                        'view' => 'payables.ledger.view',
                    ],
                    'extra' => [
                        ['label' => 'Supplier statement', 'permission' => 'payables.statement.view'],
                        ['label' => 'Aging analysis', 'permission' => 'payables.aging.view'],
                    ],
                ],
                'tax_codes' => [
                    'label' => 'Tax Codes & Rates',
                    'permissions' => [
                        'view' => 'tax.codes.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage codes & rates', 'permission' => 'tax.codes.manage'],
                    ],
                ],
                'tax_reports' => [
                    'label' => 'Tax Reports',
                    'permissions' => [
                        'view' => 'tax.reports.view',
                    ],
                ],
                'tax_ledger' => [
                    'label' => 'Tax Ledger',
                    'permissions' => [
                        'view' => 'tax.ledger.view',
                    ],
                ],
                'tax_returns' => [
                    'label' => 'Tax Returns',
                    'permissions' => [
                        'view' => 'tax.returns.manage',
                    ],
                ],
                'tax_periods' => [
                    'label' => 'Tax Periods',
                    'permissions' => [
                        'view' => 'tax.periods.view',
                    ],
                ],
                'tax_audit' => [
                    'label' => 'Tax Audit Trail',
                    'permissions' => [
                        'view' => 'tax.audit.view',
                    ],
                ],
            ],
        ],

        'communications' => [
            'label' => 'Communications',
            'entities' => [
                'templates' => [
                    'label' => 'Communication Templates',
                    'permissions' => [
                        'view' => 'communications.templates.view',
                        'create' => 'communications.templates.create',
                        'edit' => 'communications.templates.edit',
                    ],
                    'extra' => [
                        ['label' => 'View version history', 'permission' => 'communications.templates.version_view'],
                        ['label' => 'Restore template versions', 'permission' => 'communications.templates.restore'],
                    ],
                ],
                'notifications' => [
                    'label' => 'Notification Center',
                    'permissions' => [
                        'view' => 'communications.notifications.view',
                        'edit' => 'communications.notifications.manage',
                    ],
                    'extra' => [
                        ['label' => 'Administer all user notifications', 'permission' => 'communications.notifications.admin'],
                    ],
                ],
                'communication_logs' => [
                    'label' => 'Communication Logs',
                    'permissions' => [
                        'view' => 'communications.logs.view',
                    ],
                    'extra' => [
                        ['label' => 'Audit delivery events', 'permission' => 'communications.logs.audit'],
                        ['label' => 'Export logs', 'permission' => 'communications.logs.export'],
                        ['label' => 'Administer all logs', 'permission' => 'communications.logs.admin'],
                    ],
                ],
                'sms' => [
                    'label' => 'Bulk SMS',
                    'permissions' => [
                        'view' => 'communications.sms.view',
                        'create' => 'communications.sms.send',
                        'approve' => 'communications.sms.approve',
                    ],
                    'extra' => [
                        ['label' => 'Schedule campaigns', 'permission' => 'communications.sms.schedule'],
                        ['label' => 'SMS audit & credits', 'permission' => 'communications.sms.audit'],
                    ],
                ],
                'whatsapp' => [
                    'label' => 'WhatsApp',
                    'permissions' => [
                        'view' => 'communications.whatsapp.view',
                        'create' => 'communications.whatsapp.send',
                        'edit' => 'communications.whatsapp.manage',
                    ],
                    'extra' => [
                        ['label' => 'Delivery audit', 'permission' => 'communications.whatsapp.audit'],
                    ],
                ],
                'email' => [
                    'label' => 'Email Center',
                    'permissions' => [
                        'view' => 'communications.email.view',
                        'create' => 'communications.email.send',
                        'edit' => 'communications.email.manage',
                    ],
                    'extra' => [
                        ['label' => 'Schedule sends', 'permission' => 'communications.email.schedule'],
                        ['label' => 'Delivery audit', 'permission' => 'communications.email.audit'],
                    ],
                ],
                'inbox' => [
                    'label' => 'Shared Inbox',
                    'permissions' => [
                        'view' => 'communications.inbox.view',
                        'create' => 'communications.inbox.reply',
                        'edit' => 'communications.inbox.assign',
                    ],
                    'extra' => [
                        ['label' => 'Close conversations', 'permission' => 'communications.inbox.close'],
                        ['label' => 'Internal notes', 'permission' => 'communications.inbox.notes'],
                        ['label' => 'Attachments', 'permission' => 'communications.inbox.attachments'],
                        ['label' => 'Inbox audit', 'permission' => 'communications.inbox.audit'],
                        ['label' => 'Escalate conversations', 'permission' => 'communications.inbox.escalate'],
                        ['label' => 'Executive inbox view', 'permission' => 'communications.inbox.executive'],
                        ['label' => 'Inbox admin', 'permission' => 'communications.inbox.admin'],
                    ],
                ],
            ],
        ],

        'commercial' => [
            'label' => 'Commercial',
            'entities' => [
                'price_books' => [
                    'label' => 'Price Books',
                    'permissions' => [
                        'view' => 'commercial.price_books.view',
                        'create' => 'commercial.price_books.create',
                        'edit' => 'commercial.price_books.edit',
                        'delete' => 'commercial.price_books.delete',
                    ],
                ],
                'approvals' => [
                    'label' => 'Approvals Queue',
                    'permissions' => [
                        'view' => 'commercial.approvals.view',
                    ],
                    'extra' => [
                        ['label' => 'Take action', 'permission' => 'commercial.approvals.action'],
                    ],
                ],
                'complaints' => [
                    'label' => 'Complaints',
                    'permissions' => [
                        'view' => 'commercial.complaints.view',
                        'create' => 'commercial.complaints.create',
                        'edit' => 'commercial.complaints.edit',
                    ],
                    'extra' => [
                        ['label' => 'Resolve', 'permission' => 'commercial.complaints.resolve'],
                    ],
                ],
                'support_tickets' => [
                    'label' => 'Support Tickets',
                    'permissions' => [
                        'view' => 'commercial.tickets.view',
                        'create' => 'commercial.tickets.create',
                        'edit' => 'commercial.tickets.edit',
                    ],
                    'extra' => [
                        ['label' => 'Assign', 'permission' => 'commercial.tickets.assign'],
                        ['label' => 'Resolve', 'permission' => 'commercial.tickets.resolve'],
                    ],
                ],
                'sales_reports' => [
                    'label' => 'Sales Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.sales.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.sales.export'],
                        ['label' => 'Manage', 'permission' => 'commercial.reports.sales.manage'],
                    ],
                ],
                'quotation_reports' => [
                    'label' => 'Quotation Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.quotations.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.quotations.export'],
                    ],
                ],
                'sales_order_reports' => [
                    'label' => 'Sales Order Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.sales_orders.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.sales_orders.export'],
                    ],
                ],
                'customer_reports' => [
                    'label' => 'Customer Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.customers.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.customers.export'],
                    ],
                ],
                'artwork_reports' => [
                    'label' => 'Artwork Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.artwork.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.artwork.export'],
                    ],
                ],
                'conversion_reports' => [
                    'label' => 'Conversion Reports',
                    'permissions' => [
                        'view' => 'commercial.reports.conversion.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'commercial.reports.conversion.export'],
                    ],
                ],
                'report_exports' => [
                    'label' => 'Report Export Framework',
                    'permissions' => [
                        'export' => 'commercial.reports.export',
                        'view' => 'commercial.reports.exports.view',
                    ],
                    'extra' => [
                        ['label' => 'Download', 'permission' => 'commercial.reports.exports.download'],
                    ],
                ],
            ],
        ],

        'reports_intelligence' => [
            'label' => 'Reports & Intelligence',
            'entities' => [
                'reports' => [
                    'label' => 'Module Reports',
                    'permissions' => [
                        'view' => 'reports.view',
                    ],
                    'extra' => [
                        ['label' => 'Export', 'permission' => 'reports.export'],
                    ],
                ],
                'kpi_center' => [
                    'label' => 'KPI Center',
                    'permissions' => [
                        'view' => 'kpi.view',
                    ],
                    'extra' => [
                        ['label' => 'Manage KPI settings', 'permission' => 'kpi.manage'],
                    ],
                ],
                'inventory_360' => [
                    'label' => 'Inventory 360',
                    'permissions' => ['view' => 'intelligence.inventory.view'],
                ],
                'procurement_360' => [
                    'label' => 'Procurement 360',
                    'permissions' => ['view' => 'intelligence.vendor.view'],
                ],
                'branch_360' => [
                    'label' => 'Branch 360',
                    'permissions' => ['view' => 'intelligence.branch.view'],
                ],
                'production_360' => [
                    'label' => 'Production 360',
                    'permissions' => ['view' => 'intelligence.production.view'],
                ],
                'financial_360' => [
                    'label' => 'Financial 360',
                    'permissions' => ['view' => 'intelligence.financial.view'],
                ],
                'commercial_360' => [
                    'label' => 'Commercial 360',
                    'permissions' => ['view' => 'intelligence.commercial.view'],
                ],
            ],
        ],

    ],

];
