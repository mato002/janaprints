<?php

/**
 * Root sidebar navigation (workspaces only).
 * Feature links live on workspace hub pages — see config/workspaces.php.
 * Order: daily operations first → support → analysis → setup last.
 */
return [
    [
        'label' => 'Dashboard',
        'route' => 'admin.dashboard',
        'permission' => null,
        'icon' => 'home',
    ],
    [
        'label' => 'Sales',
        'route' => 'admin.workspaces.commercial',
        'workspace' => 'commercial',
        'icon' => 'shopping-cart',
    ],
    [
        'label' => 'Production',
        'route' => 'admin.workspaces.production',
        'workspace' => 'production',
        'icon' => 'cog',
    ],
    [
        'label' => 'Inventory',
        'route' => 'admin.workspaces.supply-chain',
        'workspace' => 'supply-chain',
        'icon' => 'cube',
    ],
    [
        'label' => 'Communications',
        'route' => 'admin.workspaces.communications',
        'workspace' => 'communications',
        'icon' => 'inbox',
    ],
    [
        'label' => 'Accounting',
        'route' => 'admin.workspaces.accounting',
        'workspace' => 'accounting',
        'icon' => 'currency-dollar',
    ],
    [
        'label' => 'Printing Intelligence',
        'route' => 'admin.workspaces.printing-intelligence',
        'workspace' => 'printing-intelligence',
        'permission' => null,
        'icon' => 'color-swatch',
    ],
    [
        'label' => 'Reports & Intelligence',
        'route' => 'admin.workspaces.reports',
        'workspace' => 'reports',
        'icon' => 'chart-pie',
    ],
    [
        'label' => 'Assets',
        'route' => 'admin.workspaces.assets',
        'workspace' => 'assets',
        'icon' => 'chip',
    ],
    [
        'label' => 'HR',
        'route' => 'admin.workspaces.hr',
        'workspace' => 'hr',
        'icon' => 'identification',
    ],
    [
        'label' => 'Administration',
        'route' => 'admin.workspaces.administration',
        'workspace' => 'administration',
        'icon' => 'shield-check',
    ],
];
