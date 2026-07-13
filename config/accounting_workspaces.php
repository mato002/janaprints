<?php

/**
 * Accounting workspace hub and section catalogs (presentation only).
 * Root accounting hub shows six workspace cards; features live on section pages.
 */
return [

    'hub' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'description' => 'Finance command center with KPIs, activity, and quick actions.',
            'route' => 'admin.accounting.dashboard',
            'permission' => 'accounting.dashboard.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.accounting.dashboard'],
        ],
        [
            'label' => 'General Ledger',
            'description' => 'Journals, ledger inquiry, trial balance, and financial statements.',
            'route' => 'admin.workspaces.accounting.section',
            'route_params' => ['section' => 'general-ledger'],
            'permission' => 'accounting.journals.view|accounting.reports.view',
            'icon' => 'book-open',
            'active_routes' => ['admin.workspaces.accounting.section:general-ledger', 'admin.accounting.journals.*', 'admin.accounting.ledger.*', 'admin.accounting.reports.*', 'admin.accounting.trial-balance.*'],
        ],
        [
            'label' => 'Receivables',
            'description' => 'Customer invoices, payments, ledger, statements, and aging.',
            'route' => 'admin.workspaces.accounting.section',
            'route_params' => ['section' => 'receivables'],
            'permission' => 'invoices.view|payments.view|receivables.ledger.view',
            'icon' => 'users',
            'active_routes' => ['admin.workspaces.accounting.section:receivables', 'admin.invoices.*', 'admin.payments.*', 'admin.receivables.*'],
        ],
        [
            'label' => 'Payables',
            'description' => 'Supplier bills, payments, ledger, statements, and AP aging.',
            'route' => 'admin.workspaces.accounting.section',
            'route_params' => ['section' => 'payables'],
            'permission' => 'payables.bills.view|payables.payments.view|payables.ledger.view',
            'icon' => 'truck',
            'active_routes' => ['admin.workspaces.accounting.section:payables', 'admin.payables.*'],
        ],
        [
            'label' => 'Cash Management',
            'description' => 'Bank accounts, reconciliation, and cash flow statement.',
            'route' => 'admin.workspaces.accounting.section',
            'route_params' => ['section' => 'cash-management'],
            'permission' => 'accounting.bank.view|accounting.reports.view',
            'icon' => 'cash',
            'active_routes' => ['admin.workspaces.accounting.section:cash-management', 'admin.accounting.bank.*', 'admin.accounting.reports.cash-flow'],
        ],
        [
            'label' => 'Tax',
            'description' => 'Tax codes, ledger, VAT summary, returns, periods, and audit trail.',
            'route' => 'admin.workspaces.accounting.section',
            'route_params' => ['section' => 'tax'],
            'permission' => 'tax.codes.view|tax.ledger.view|tax.reports.view',
            'icon' => 'receipt-tax',
            'active_routes' => ['admin.workspaces.accounting.section:tax', 'admin.tax.*'],
        ],
        [
            'label' => 'Budgets',
            'description' => 'GL budgets and budget versus actual reporting.',
            'route' => 'admin.workspaces.accounting.section',
            'route_params' => ['section' => 'budgets'],
            'permission' => 'accounting.budgets.view',
            'icon' => 'chart-bar',
            'active_routes' => ['admin.workspaces.accounting.section:budgets', 'admin.accounting.budgets.*'],
        ],
        [
            'label' => 'Setup',
            'description' => 'Chart of accounts, fiscal periods, posting rules, and templates.',
            'route' => 'admin.workspaces.accounting.section',
            'route_params' => ['section' => 'setup'],
            'permission' => 'accounting.chart.view|accounting.periods.view|accounting.posting.view|accounting.currencies.view',
            'icon' => 'cog',
            'active_routes' => ['admin.workspaces.accounting.section:setup', 'admin.accounting.accounts.*', 'admin.accounting.periods.*', 'admin.accounting.posting.*', 'admin.accounting.currencies.*'],
        ],
    ],

    'sections' => [

        'general-ledger' => [
            'title' => 'General Ledger',
            'description' => 'Journal entries, ledger inquiry, trial balance, and statutory financial reports.',
            'icon' => 'book-open',
            'groups' => [
                [
                    'label' => 'Financial Reports',
                    'items' => [
                        ['label' => 'Journals', 'description' => 'Manual and system journal entries.', 'route' => 'admin.accounting.journals.index', 'permission' => 'accounting.journals.view', 'icon' => 'document-text', 'active_routes' => ['admin.accounting.journals.*']],
                        ['label' => 'General Ledger', 'description' => 'Posted transactions by account.', 'route' => 'admin.accounting.ledger.index', 'permission' => 'accounting.journals.view', 'icon' => 'book-open', 'active_routes' => ['admin.accounting.ledger.*']],
                        ['label' => 'Trial Balance', 'description' => 'Debit and credit trial balance.', 'route' => 'admin.accounting.reports.trial-balance', 'permission' => 'accounting.reports.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.accounting.reports.trial-balance', 'admin.accounting.trial-balance.index']],
                        ['label' => 'Balance Sheet', 'description' => 'Assets, liabilities, and equity.', 'route' => 'admin.accounting.reports.balance-sheet', 'permission' => 'accounting.reports.view', 'icon' => 'scale', 'active_routes' => ['admin.accounting.reports.balance-sheet']],
                        ['label' => 'Profit & Loss', 'description' => 'Revenue and expenses for a period.', 'route' => 'admin.accounting.reports.profit-and-loss', 'permission' => 'accounting.reports.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.accounting.reports.profit-and-loss']],
                        ['label' => 'Cash Flow Statement', 'description' => 'Operating, investing, and financing cash movements.', 'route' => 'admin.accounting.reports.cash-flow', 'permission' => 'accounting.reports.view', 'icon' => 'cash', 'active_routes' => ['admin.accounting.reports.cash-flow']],
                        ['label' => 'GL Report', 'description' => 'Account ledger with running balance.', 'route' => 'admin.accounting.reports.general-ledger', 'permission' => 'accounting.reports.view', 'icon' => 'document-text', 'active_routes' => ['admin.accounting.reports.general-ledger']],
                        ['label' => 'Financial Integrity', 'description' => 'Trial balance and balance sheet integrity checks.', 'route' => 'admin.accounting.reports.financial-integrity', 'permission' => 'accounting.reports.view', 'icon' => 'shield-check', 'active_routes' => ['admin.accounting.reports.financial-integrity']],
                    ],
                ],
            ],
        ],

        'receivables' => [
            'title' => 'Receivables',
            'description' => 'Customer billing, collections, and accounts receivable analysis.',
            'icon' => 'users',
            'groups' => [
                [
                    'label' => 'Customer AR',
                    'items' => [
                        ['label' => 'Invoices', 'description' => 'Customer billing and credit notes.', 'route' => 'admin.invoices.index', 'permission' => 'invoices.view', 'icon' => 'receipt-tax', 'active_routes' => ['admin.invoices.*']],
                        ['label' => 'Payments', 'description' => 'Cash, bank, and M-Pesa receipts.', 'route' => 'admin.payments.index', 'permission' => 'payments.view', 'icon' => 'credit-card', 'active_routes' => ['admin.payments.*']],
                        ['label' => 'Customer Deposits', 'description' => 'Unallocated deposits, applications, and refunds.', 'route' => 'admin.deposits.index', 'permission' => 'payments.view', 'icon' => 'cash', 'active_routes' => ['admin.deposits.*']],
                        ['label' => 'Customer Ledger', 'description' => 'Running AR balance by customer.', 'route' => 'admin.receivables.ledger', 'permission' => 'receivables.ledger.view', 'icon' => 'book-open', 'active_routes' => ['admin.receivables.ledger']],
                        ['label' => 'Customer Statement', 'description' => 'Period statement of account.', 'route' => 'admin.receivables.statement', 'permission' => 'receivables.statement.view', 'icon' => 'document-text', 'active_routes' => ['admin.receivables.statement']],
                        ['label' => 'Aging Analysis', 'description' => 'Outstanding balances by age bucket.', 'route' => 'admin.receivables.aging', 'permission' => 'receivables.aging.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.receivables.aging']],
                        ['label' => 'AR Reconciliation', 'description' => 'Subledger vs GL truth checks before period close.', 'route' => 'admin.receivables.reconciliation', 'permission' => 'receivables.reconciliation.view|receivables.ledger.view', 'icon' => 'scale', 'active_routes' => ['admin.receivables.reconciliation']],
                    ],
                ],
            ],
        ],

        'payables' => [
            'title' => 'Payables',
            'description' => 'Supplier bills, disbursements, and accounts payable analysis.',
            'icon' => 'truck',
            'groups' => [
                [
                    'label' => 'Supplier AP',
                    'items' => [
                        ['label' => 'Supplier Bills', 'description' => 'Supplier invoices and credit notes.', 'route' => 'admin.payables.bills.index', 'permission' => 'payables.bills.view', 'icon' => 'receipt-tax', 'active_routes' => ['admin.payables.bills.*']],
                        ['label' => 'Supplier Payments', 'description' => 'Pay trade creditors and allocate to bills.', 'route' => 'admin.payables.payments.index', 'permission' => 'payables.payments.view', 'icon' => 'credit-card', 'active_routes' => ['admin.payables.payments.*']],
                        ['label' => 'Supplier Ledger', 'description' => 'Running AP balance by supplier.', 'route' => 'admin.payables.ledger', 'permission' => 'payables.ledger.view', 'icon' => 'book-open', 'active_routes' => ['admin.payables.ledger']],
                        ['label' => 'Supplier Statement', 'description' => 'Period statement of account.', 'route' => 'admin.payables.statement', 'permission' => 'payables.statement.view', 'icon' => 'document-text', 'active_routes' => ['admin.payables.statement']],
                        ['label' => 'AP Aging', 'description' => 'Outstanding balances by age bucket.', 'route' => 'admin.payables.aging', 'permission' => 'payables.aging.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.payables.aging']],
                    ],
                ],
            ],
        ],

        'cash-management' => [
            'title' => 'Cash Management',
            'description' => 'Bank accounts, statement reconciliation, and cash flow reporting.',
            'icon' => 'cash',
            'groups' => [
                [
                    'label' => 'Cash & Bank',
                    'items' => [
                        ['label' => 'Bank Accounts', 'description' => 'GL-linked bank and cash accounts.', 'route' => 'admin.accounting.bank.accounts.index', 'permission' => 'accounting.bank.view', 'icon' => 'cash', 'active_routes' => ['admin.accounting.bank.accounts.*']],
                        ['label' => 'Bank Reconciliation', 'description' => 'Match statements to posted GL movements.', 'route' => 'admin.accounting.bank.reconciliation.index', 'permission' => 'accounting.bank.view', 'icon' => 'scale', 'active_routes' => ['admin.accounting.bank.reconciliation.*']],
                        ['label' => 'Cash Flow Statement', 'description' => 'Operating, investing, and financing cash movements.', 'route' => 'admin.accounting.reports.cash-flow', 'permission' => 'accounting.reports.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.accounting.reports.cash-flow']],
                    ],
                ],
            ],
        ],

        'budgets' => [
            'title' => 'Budgets',
            'description' => 'GL budgets and budget versus actual analysis.',
            'icon' => 'chart-bar',
            'groups' => [
                [
                    'label' => 'Planning',
                    'items' => [
                        ['label' => 'Budgets', 'description' => 'Create and activate GL budgets.', 'route' => 'admin.accounting.budgets.index', 'permission' => 'accounting.budgets.view', 'icon' => 'document-text', 'active_routes' => ['admin.accounting.budgets.*']],
                    ],
                ],
            ],
        ],

        'tax' => [
            'title' => 'Tax',
            'description' => 'VAT configuration, tax ledger, returns, and compliance reporting.',
            'icon' => 'receipt-tax',
            'groups' => [
                [
                    'label' => 'Tax Compliance',
                    'items' => [
                        ['label' => 'Tax Codes', 'description' => 'VAT, WHT, zero-rated, and exempt codes.', 'route' => 'admin.tax.codes.index', 'permission' => 'tax.codes.view', 'icon' => 'receipt-tax', 'active_routes' => ['admin.tax.codes.*']],
                        ['label' => 'Tax Ledger', 'description' => 'Posted tax transactions by document.', 'route' => 'admin.tax.ledger.index', 'permission' => 'tax.ledger.view', 'icon' => 'book-open', 'active_routes' => ['admin.tax.ledger.*']],
                        ['label' => 'VAT Summary', 'description' => 'Output, input, and net liability.', 'route' => 'admin.tax.reports.vat-summary', 'permission' => 'tax.reports.view', 'icon' => 'chart-pie', 'active_routes' => ['admin.tax.reports.*']],
                        ['label' => 'Tax Returns', 'description' => 'Period VAT return drafts and filing.', 'route' => 'admin.tax.returns.index', 'permission' => 'tax.returns.manage', 'icon' => 'document-text', 'active_routes' => ['admin.tax.returns.*']],
                        ['label' => 'Tax Periods', 'description' => 'Reporting periods for tax compliance.', 'route' => 'admin.tax.periods.index', 'permission' => 'tax.periods.view', 'icon' => 'calendar', 'active_routes' => ['admin.tax.periods.*']],
                        ['label' => 'Tax Audit Trail', 'description' => 'Changes to tax configuration.', 'route' => 'admin.tax.audit.index', 'permission' => 'tax.audit.view', 'icon' => 'shield-check', 'active_routes' => ['admin.tax.audit.*']],
                    ],
                ],
            ],
        ],

        'setup' => [
            'title' => 'Accounting Setup',
            'description' => 'Chart of accounts, fiscal calendar, and automated posting configuration.',
            'icon' => 'cog',
            'groups' => [
                [
                    'label' => 'Configuration',
                    'items' => [
                        ['label' => 'Chart of Accounts', 'description' => 'Account structure and categories.', 'route' => 'admin.accounting.accounts.index', 'permission' => 'accounting.chart.view', 'icon' => 'book-open', 'active_routes' => ['admin.accounting.accounts.*']],
                        ['label' => 'Accounting Periods', 'description' => 'Fiscal years, monthly periods, and close controls.', 'route' => 'admin.accounting.periods.index', 'permission' => 'accounting.periods.view', 'icon' => 'calendar', 'active_routes' => ['admin.accounting.periods.*']],
                        ['label' => 'Posting Rules', 'description' => 'Event-to-template mapping for automated journals.', 'route' => 'admin.accounting.posting.rules.index', 'permission' => 'accounting.posting_rules.view|accounting.posting_rules.audit|accounting.posting.view', 'icon' => 'cog', 'active_routes' => ['admin.accounting.posting.rules.*']],
                        ['label' => 'Posting Templates', 'description' => 'Reusable debit/credit line definitions.', 'route' => 'admin.accounting.posting.templates.index', 'permission' => 'accounting.posting.view', 'icon' => 'document-duplicate', 'active_routes' => ['admin.accounting.posting.templates.*']],
                        ['label' => 'Currencies', 'description' => 'Active currencies and base currency.', 'route' => 'admin.accounting.currencies.index', 'permission' => 'accounting.currencies.view', 'icon' => 'cash', 'active_routes' => ['admin.accounting.currencies.index']],
                        ['label' => 'Exchange Rates', 'description' => 'FX rates to the company base currency.', 'route' => 'admin.accounting.currencies.rates.index', 'permission' => 'accounting.currencies.view', 'icon' => 'scale', 'active_routes' => ['admin.accounting.currencies.rates.*']],
                    ],
                ],
            ],
        ],

    ],

];
