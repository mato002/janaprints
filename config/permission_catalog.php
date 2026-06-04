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

    ],

];
