<?php

/**
 * Root sidebar navigation (workspaces only).
 * Feature links live on workspace hub pages — see config/workspaces.php.
 */
return [
    [
        'label' => 'Dashboard',
        'route' => 'admin.dashboard',
        'permission' => null,
        'icon' => 'home',
    ],
    [
        'label' => 'Commercial',
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
        'label' => 'Printing Intelligence',
        'route' => 'admin.workspaces.printing-intelligence',
        'workspace' => 'printing-intelligence',
        'permission' => null,
        'icon' => 'color-swatch',
    ],
    [
        'label' => 'Supply Chain',
        'route' => 'admin.workspaces.supply-chain',
        'workspace' => 'supply-chain',
        'icon' => 'cube',
    ],
    [
        'label' => 'Accounting',
        'route' => 'admin.workspaces.accounting',
        'workspace' => 'accounting',
        'icon' => 'currency-dollar',
    ],
    [
        'label' => 'HR',
        'route' => 'admin.workspaces.hr',
        'workspace' => 'hr',
        'icon' => 'identification',
    ],
    [
        'label' => 'Assets',
        'route' => 'admin.workspaces.assets',
        'workspace' => 'assets',
        'icon' => 'chip',
    ],
    [
        'label' => 'Communications',
        'route' => 'admin.workspaces.communications',
        'workspace' => 'communications',
        'icon' => 'inbox',
    ],
    [
        'label' => 'Reports & Intelligence',
        'route' => 'admin.workspaces.reports',
        'workspace' => 'reports',
        'icon' => 'chart-pie',
    ],
    [
        'label' => 'Administration',
        'route' => 'admin.workspaces.administration',
        'workspace' => 'administration',
        'icon' => 'shield-check',
    ],
];
