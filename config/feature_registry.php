<?php

/**
 * Global Feature Finder registry configuration.
 *
 * Feature entries are built dynamically from workspace catalogs (config/*_workspaces.php).
 * This file defines keyword aliases, category detection, and discovery limits only.
 */
return [

    'recent_limit' => 20,

    'favorites_limit' => 12,

    'results_limit' => 24,

    /**
     * Expand search queries with synonymous terms (registry-driven, not hard-coded results).
     *
     * @var array<string, list<string>>
     */
    'keyword_aliases' => [
        'form' => ['forms', 'field', 'fields', 'dynamic', 'controls'],
        'approval' => ['approvals', 'workflow', 'workflows', 'pipeline', 'pipelines', 'governance'],
        'number' => ['numbering', 'sequence', 'sequences', 'prefix'],
        'branch' => ['branches', 'location', 'locations'],
        'tax' => ['vat', 'taxes', 'withholding'],
        'stock' => ['inventory', 'warehouse', 'warehouses', 'store'],
        'supplier' => ['suppliers', 'vendor', 'vendors', 'procurement'],
        'customer' => ['customers', 'crm', 'client', 'clients'],
        'job' => ['jobs', 'production', 'manufacturing', 'jobcard', 'job-card'],
        'quote' => ['quotes', 'quotation', 'quotations', 'estimate'],
        'report' => ['reports', 'analytics', 'intelligence', 'dashboard', 'statement'],
        'setting' => ['settings', 'configuration', 'config', 'preferences'],
        'user' => ['users', 'accounts', 'identity'],
        'employee' => ['employees', 'staff', 'hr', 'workforce'],
        'asset' => ['assets', 'fixed-asset', 'depreciation'],
        'ledger' => ['gl', 'general-ledger', 'journal', 'journals'],
        'invoice' => ['invoices', 'billing', 'receivable', 'ar'],
        'payment' => ['payments', 'payable', 'ap', 'treasury'],
        'dispatch' => ['delivery', 'deliveries', 'shipment', 'shipments'],
        'quality' => ['qc', 'inspection', 'inspections'],
        'schedule' => ['scheduling', 'calendar', 'planning'],
        'pos' => ['point-of-sale', 'counter', 'retail'],
        'sms' => ['communications', 'campaign', 'campaigns', 'notification', 'notifications'],
    ],

    /**
     * Map feature signals to command-palette categories.
     *
     * @var array<string, list<string>>
     */
    'category_rules' => [
        'reports' => ['report', 'reports', 'analytics', 'intelligence', 'dashboard', 'statement', 'valuation', 'kpi'],
        'settings' => ['setting', 'settings', 'configuration', 'numbering', 'branding', 'preference'],
        'workflows' => ['approval', 'workflow', 'pipeline', 'governance', 'delegation', 'escalation'],
        'workspaces' => ['workspace', 'hub', 'section'],
    ],

    /**
     * Workspace config keys indexed for discovery (auto-synced with ModuleShellPresenter).
     *
     * @var list<string>
     */
    'workspace_sources' => [
        'commercial_workspaces',
        'production_workspaces',
        'supply_chain_workspaces',
        'accounting_workspaces',
        'hr_workspaces',
        'assets_workspaces',
        'communications_workspaces',
        'reports_workspaces',
        'administration_workspaces',
        'dispatch_workspaces',
    ],

];
