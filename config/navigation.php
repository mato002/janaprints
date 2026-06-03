<?php

return [
    [
        'label' => 'Dashboard',
        'route' => 'admin.dashboard',
        'permission' => null,
        'icon' => 'home',
    ],
    [
        'label' => 'CRM',
        'icon' => 'users',
        'children' => [
            ['label' => 'Customers', 'route' => 'admin.crm.customers.index', 'permission' => 'crm.customers.view', 'icon' => 'user-circle'],
            ['label' => 'Leads', 'route' => 'admin.crm.leads.index', 'permission' => 'crm.leads.view', 'icon' => 'sparkles'],
            ['label' => 'Segments', 'route' => 'admin.crm.segments.index', 'permission' => 'crm.customers.view', 'icon' => 'tag'],
        ],
    ],
    [
        'label' => 'Sales',
        'icon' => 'shopping-cart',
        'children' => [
            ['label' => 'Quotations', 'route' => 'admin.quotations.dashboard', 'permission' => 'quotations.view', 'icon' => 'document-text'],
            ['label' => 'Sales Orders', 'route' => 'admin.sales-orders.dashboard', 'permission' => 'sales_orders.view', 'icon' => 'clipboard-list'],
            ['label' => 'Invoices', 'coming_soon' => true, 'icon' => 'receipt-tax'],
        ],
    ],
    [
        'label' => 'Artwork',
        'icon' => 'color-swatch',
        'children' => [
            ['label' => 'Dashboard', 'route' => 'admin.artwork.dashboard', 'permission' => 'artwork.view', 'icon' => 'color-swatch'],
            ['label' => 'Requests', 'route' => 'admin.artwork.index', 'permission' => 'artwork.view', 'icon' => 'photograph'],
        ],
    ],
    [
        'label' => 'Production',
        'icon' => 'cog',
        'children' => [
            ['label' => 'Dashboard', 'route' => 'admin.production.dashboard', 'permission' => 'production.view', 'icon' => 'cog'],
            ['label' => 'Job Cards', 'route' => 'admin.production.job-cards.index', 'permission' => 'production.view', 'icon' => 'collection'],
            ['label' => 'Work Centers', 'route' => 'admin.production.work-centers.index', 'permission' => 'production.view', 'icon' => 'chip'],
        ],
    ],
    [
        'label' => 'Inventory',
        'icon' => 'cube',
        'children' => [
            ['label' => 'Dashboard', 'route' => 'admin.inventory.dashboard', 'permission' => 'inventory.view', 'icon' => 'cube'],
            ['label' => 'Items', 'route' => 'admin.inventory.items.index', 'permission' => 'inventory.view', 'icon' => 'template'],
            ['label' => 'Receipts', 'route' => 'admin.inventory.receipts.index', 'permission' => 'inventory.view', 'icon' => 'archive'],
            ['label' => 'Issues', 'route' => 'admin.inventory.issues.index', 'permission' => 'inventory.view', 'icon' => 'switch-horizontal'],
            ['label' => 'Movements', 'route' => 'admin.inventory.movements.index', 'permission' => 'inventory.view', 'icon' => 'switch-horizontal'],
        ],
    ],
    [
        'label' => 'Procurement',
        'icon' => 'truck',
        'children' => [
            ['label' => 'Suppliers', 'coming_soon' => true, 'icon' => 'office-building'],
            ['label' => 'Purchases', 'coming_soon' => true, 'icon' => 'shopping-bag'],
        ],
    ],
    [
        'label' => 'Finance',
        'icon' => 'currency-dollar',
        'children' => [
            ['label' => 'Dashboard', 'coming_soon' => true, 'icon' => 'chart-pie'],
            ['label' => 'Receivables', 'coming_soon' => true, 'icon' => 'cash'],
            ['label' => 'Payables', 'coming_soon' => true, 'icon' => 'credit-card'],
            ['label' => 'Ledger', 'coming_soon' => true, 'icon' => 'book-open'],
        ],
    ],
    [
        'label' => 'Dispatch',
        'icon' => 'location-marker',
        'children' => [
            ['label' => 'Deliveries', 'coming_soon' => true, 'icon' => 'truck'],
            ['label' => 'Tracking', 'coming_soon' => true, 'icon' => 'map'],
        ],
    ],
    [
        'label' => 'Organization',
        'icon' => 'office-building',
        'children' => [
            ['label' => 'Companies', 'route' => 'admin.companies.index', 'permission' => 'companies.manage', 'icon' => 'building'],
            ['label' => 'Branches', 'route' => 'admin.branches.index', 'permission' => 'branches.manage', 'icon' => 'location-marker'],
            ['label' => 'Departments', 'route' => 'admin.departments.index', 'permission' => 'departments.manage', 'icon' => 'view-grid'],
            ['label' => 'Employees', 'route' => 'admin.employees.index', 'permission' => 'employees.manage', 'icon' => 'identification'],
        ],
    ],
    [
        'label' => 'Administration',
        'icon' => 'shield-check',
        'children' => [
            ['label' => 'Users', 'route' => 'admin.users.index', 'permission' => 'users.view', 'icon' => 'users'],
            ['label' => 'Access Control', 'route' => 'admin.access-control.index', 'permission' => 'users.view|roles.view', 'icon' => 'shield-check', 'active_routes' => ['admin.access-control.*', 'admin.roles.*']],
            ['label' => 'Activity Logs', 'route' => 'admin.activity-logs.index', 'permission' => 'activity_logs.view', 'icon' => 'clock'],
            ['label' => 'Settings', 'route' => 'admin.settings.index', 'permission' => 'settings.view', 'icon' => 'cog'],
        ],
    ],
];
