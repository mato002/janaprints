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

    ],

];
