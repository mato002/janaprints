<?php

/**
 * Supply Chain workspace hub and section catalogs (presentation only).
 *
 * Store registers are modes of Store Desk. Analytics live under Reports & Intelligence.
 * Payables and Assets are owned by Accounting / Assets modules.
 */
return [

    'hub' => [
        [
            'label' => 'Catalogue',
            'description' => 'Products, categories, brands, attributes, and price lists.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'catalogue'],
            'permission' => 'catalogue.view',
            'icon' => 'template',
            'active_routes' => ['admin.workspaces.supply-chain.section:catalogue', 'admin.inventory.catalogue.*', 'admin.inventory.items.*'],
        ],
        [
            'label' => 'Store',
            'description' => 'Store desk — receive, issue, transfer, adjust, and balances.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'store-operations'],
            'permission' => 'inventory.view',
            'icon' => 'building',
            'active_routes' => [
                'admin.workspaces.supply-chain.section:store-operations',
                'admin.store.desk',
                'admin.store.desk.*',
                'admin.inventory.store.*',
                'admin.inventory.warehouses.*',
                'admin.inventory.transfers.*',
                'admin.inventory.adjustments.*',
                'admin.inventory.movements.*',
                'admin.inventory.receipts.*',
                'admin.inventory.issues.*',
                'admin.inventory.alerts.*',
                'admin.inventory.reorder-settings.*',
                'admin.inventory.virtual-locations.*',
            ],
        ],
        [
            'label' => 'Procurement',
            'description' => 'Buy desk — requests, suppliers, RFQs, orders, and receipts.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'procurement'],
            'permission' => 'procurement.vendors.view',
            'icon' => 'truck',
            'active_routes' => ['admin.workspaces.supply-chain.section:procurement', 'admin.procurement.*'],
        ],
        [
            'label' => 'Inventory Control',
            'description' => 'Stock counts, cycle counts, variance, and reconciliation.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'inventory-control'],
            'permission' => 'inventory.view',
            'icon' => 'clipboard-list',
            'active_routes' => [
                'admin.workspaces.supply-chain.section:inventory-control',
                'admin.inventory.stock-counts.*',
                'admin.inventory.cycle-counts.*',
                'admin.inventory.variances.*',
                'admin.inventory.reconciliations.*',
                'admin.inventory.variance-reason-codes.*',
            ],
        ],
        [
            'label' => 'Costing',
            'description' => 'Inventory valuation and production job costing.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'costing'],
            'permission' => 'inventory.valuation.view|production.costing.view',
            'icon' => 'currency-dollar',
            'active_routes' => ['admin.workspaces.supply-chain.section:costing', 'admin.inventory.valuation.*', 'admin.production.costing.*'],
        ],
    ],

    'sections' => [

        'catalogue' => [
            'title' => 'Catalogue',
            'description' => 'Products and price lists — master data under Setup.',
            'icon' => 'template',
            'quick_actions' => [
                ['label' => 'New Product', 'route' => 'admin.inventory.items.create', 'permission' => 'catalogue.create'],
                ['label' => 'New Price List', 'route' => 'admin.inventory.catalogue.price-lists.create', 'permission' => 'catalogue.create'],
            ],
            'groups' => [
                [
                    'label' => 'Catalogue',
                    'items' => [
                        [
                            'key' => 'products',
                            'label' => 'Products',
                            'description' => 'Inventory items and finished goods.',
                            'route' => 'admin.inventory.items.index',
                            'permission' => 'catalogue.view',
                            'icon' => 'template',
                            'active_routes' => [
                                'admin.inventory.items.*',
                                'admin.inventory.catalogue.dashboard',
                            ],
                        ],
                        [
                            'key' => 'price-lists',
                            'label' => 'Price Lists',
                            'description' => 'Sell and cost price lists for catalogue items.',
                            'route' => 'admin.inventory.catalogue.price-lists.index',
                            'permission' => 'catalogue.view',
                            'icon' => 'currency-dollar',
                            'active_routes' => ['admin.inventory.catalogue.price-lists.*'],
                        ],
                    ],
                ],
                [
                    'label' => 'Setup',
                    'items' => [
                        [
                            'label' => 'Categories',
                            'description' => 'Product categories and default units.',
                            'route' => 'admin.inventory.catalogue.categories.index',
                            'permission' => 'catalogue.view',
                            'icon' => 'collection',
                            'active_routes' => ['admin.inventory.catalogue.categories.*'],
                        ],
                        [
                            'label' => 'Subcategories',
                            'description' => 'Category subdivisions for catalogue items.',
                            'route' => 'admin.inventory.catalogue.subcategories.index',
                            'permission' => 'catalogue.view',
                            'icon' => 'collection',
                            'active_routes' => ['admin.inventory.catalogue.subcategories.*'],
                        ],
                        [
                            'label' => 'Brands',
                            'description' => 'Brand master data for products.',
                            'route' => 'admin.inventory.catalogue.brands.index',
                            'permission' => 'catalogue.view',
                            'icon' => 'tag',
                            'active_routes' => ['admin.inventory.catalogue.brands.*'],
                        ],
                        [
                            'label' => 'Attributes',
                            'description' => 'Size, color, GSM, and other item attributes.',
                            'route' => 'admin.inventory.catalogue.attributes.index',
                            'permission' => 'catalogue.view',
                            'icon' => 'adjustments',
                            'active_routes' => ['admin.inventory.catalogue.attributes.*'],
                        ],
                        [
                            'label' => 'Units of Measure',
                            'description' => 'UOM codes used across catalogue and store.',
                            'route' => 'admin.inventory.catalogue.units.index',
                            'permission' => 'catalogue.view',
                            'icon' => 'scale',
                            'active_routes' => ['admin.inventory.catalogue.units.*'],
                        ],
                    ],
                ],
            ],
        ],

        'store-operations' => [
            'title' => 'Store',
            'description' => 'Store Desk for day-to-day stock work — Setup holds warehouses and permissions.',
            'icon' => 'building',
            'quick_actions' => [
                ['label' => 'Open Store Desk', 'route' => 'admin.workspaces.supply-chain.section', 'route_params' => ['section' => 'store-operations', 'tab' => 'store-desk'], 'permission' => 'inventory.view'],
                ['label' => 'New Receipt', 'route' => 'admin.inventory.receipts.create', 'permission' => 'inventory.create'],
                ['label' => 'New Issue', 'route' => 'admin.inventory.issues.create', 'permission' => 'inventory.create'],
            ],
            'groups' => [
                [
                    'label' => 'Store',
                    'items' => [
                        [
                            'key' => 'store-desk',
                            'label' => 'Store Desk',
                            'description' => 'Receive, issue, transfer, adjust — plus balances, movements, and alerts.',
                            'route' => 'admin.workspaces.supply-chain.section',
                            'route_params' => ['section' => 'store-operations', 'tab' => 'store-desk'],
                            'permission' => 'inventory.view',
                            'icon' => 'building',
                            'active_routes' => [
                                'admin.store.desk',
                                'admin.store.desk.*',
                                'admin.inventory.store.balances',
                                'admin.inventory.store.dashboard',
                                'admin.inventory.receipts.*',
                                'admin.inventory.issues.*',
                                'admin.inventory.transfers.*',
                                'admin.inventory.adjustments.*',
                                'admin.inventory.movements.*',
                                'admin.inventory.alerts.*',
                            ],
                            'modes' => [
                                [
                                    'key' => 'desk',
                                    'label' => 'Desk',
                                    'route' => 'admin.store.desk',
                                    'active_routes' => ['admin.store.desk', 'admin.store.desk.*', 'admin.inventory.store.dashboard'],
                                ],
                                [
                                    'key' => 'products',
                                    'label' => 'Products',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'products'],
                                    'permission' => 'catalogue.view|inventory.view',
                                    'active_routes' => ['admin.inventory.items.*', 'admin.inventory.catalogue.dashboard'],
                                ],
                                [
                                    'key' => 'balances',
                                    'label' => 'Balances',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'balances'],
                                    'active_routes' => ['admin.inventory.store.balances'],
                                ],
                                [
                                    'key' => 'receipts',
                                    'label' => 'Receipts',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'receipts'],
                                    'active_routes' => ['admin.inventory.receipts.*'],
                                ],
                                [
                                    'key' => 'issues',
                                    'label' => 'Issues',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'issues'],
                                    'active_routes' => ['admin.inventory.issues.*'],
                                ],
                                [
                                    'key' => 'transfers',
                                    'label' => 'Transfers',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'transfers'],
                                    'active_routes' => ['admin.inventory.transfers.*'],
                                ],
                                [
                                    'key' => 'adjustments',
                                    'label' => 'Adjustments',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'adjustments'],
                                    'active_routes' => ['admin.inventory.adjustments.*'],
                                ],
                                [
                                    'key' => 'movements',
                                    'label' => 'Movements',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'movements'],
                                    'active_routes' => ['admin.inventory.movements.*'],
                                ],
                                [
                                    'key' => 'alerts',
                                    'label' => 'Alerts',
                                    'route' => 'admin.store.desk',
                                    'route_params' => ['view' => 'alerts'],
                                    'permission' => 'inventory.reorder.view|inventory.view',
                                    'active_routes' => ['admin.inventory.alerts.*'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'Setup',
                    'items' => [
                        ['label' => 'Warehouses', 'description' => 'Branch stores, warehouse details, and managers.', 'route' => 'admin.inventory.warehouses.index', 'permission' => 'inventory.view', 'icon' => 'building', 'active_routes' => ['admin.inventory.warehouses.*']],
                        ['label' => 'Virtual Locations', 'description' => 'Raw, WIP, finished goods, in-transit, and quarantine buckets.', 'route' => 'admin.inventory.virtual-locations.index', 'permission' => 'inventory.virtual-locations.view', 'icon' => 'collection', 'active_routes' => ['admin.inventory.virtual-locations.*']],
                        ['label' => 'Reorder Settings', 'description' => 'Per-warehouse min/max levels and reorder quantities.', 'route' => 'admin.inventory.reorder-settings.index', 'permission' => 'inventory.reorder.configure', 'icon' => 'adjustments', 'active_routes' => ['admin.inventory.reorder-settings.*']],
                        ['label' => 'Store Permissions', 'description' => 'Role coverage for receive, issue, transfer, and adjust.', 'route' => 'admin.inventory.store.permissions', 'permission' => 'inventory.view', 'icon' => 'shield-check', 'active_routes' => ['admin.inventory.store.permissions']],
                    ],
                ],
            ],
        ],

        'procurement' => [
            'title' => 'Procurement',
            'description' => 'Buy desk for requests, sourcing, orders, and receipts. Supplier performance lives under Reports.',
            'icon' => 'truck',
            'quick_actions' => [
                ['label' => 'Open Buy Desk', 'route' => 'admin.workspaces.supply-chain.section', 'route_params' => ['section' => 'procurement', 'tab' => 'buy-desk'], 'permission' => 'procurement.vendors.view'],
                ['label' => 'New Request', 'route' => 'admin.procurement.requests.create', 'permission' => 'procurement.requests.create'],
                ['label' => 'New Supplier', 'route' => 'admin.procurement.vendors.create', 'permission' => 'procurement.vendors.create'],
            ],
            'groups' => [
                [
                    'label' => 'Procurement',
                    'items' => [
                        [
                            'key' => 'buy-desk',
                            'label' => 'Buy Desk',
                            'description' => 'Requests, suppliers, RFQs, orders, receipts, and approvals — one buying command center.',
                            'route' => 'admin.workspaces.supply-chain.section',
                            'route_params' => ['section' => 'procurement', 'tab' => 'buy-desk'],
                            'permission' => 'procurement.vendors.view',
                            'icon' => 'truck',
                            'active_routes' => [
                                'admin.procurement.desk',
                                'admin.procurement.dashboard',
                                'admin.procurement.requests.*',
                                'admin.procurement.vendors.*',
                                'admin.procurement.rfqs.*',
                                'admin.procurement.vendor-comparison.*',
                                'admin.procurement.quotations.*',
                                'admin.procurement.orders.*',
                                'admin.procurement.receipts.*',
                                'admin.procurement.approvals.*',
                            ],
                            'toolbar_actions' => [
                                [
                                    'label' => 'New request',
                                    'route' => 'admin.procurement.requests.create',
                                    'permission' => 'procurement.requests.create',
                                ],
                                [
                                    'label' => 'Create vendor',
                                    'route' => 'admin.procurement.vendors.create',
                                    'permission' => 'procurement.vendors.create',
                                    'modal' => true,
                                ],
                            ],
                            'modes' => [
                                [
                                    'key' => 'desk',
                                    'label' => 'Desk',
                                    'route' => 'admin.procurement.desk',
                                    'active_routes' => ['admin.procurement.desk', 'admin.procurement.dashboard'],
                                ],
                                [
                                    'key' => 'requests',
                                    'label' => 'Requests',
                                    'route' => 'admin.procurement.desk',
                                    'route_params' => ['view' => 'requests'],
                                    'permission' => 'procurement.requests.view',
                                    'active_routes' => ['admin.procurement.requests.*'],
                                ],
                                [
                                    'key' => 'suppliers',
                                    'label' => 'Suppliers',
                                    'route' => 'admin.procurement.desk',
                                    'route_params' => ['view' => 'suppliers'],
                                    'active_routes' => ['admin.procurement.vendors.*'],
                                ],
                                [
                                    'key' => 'rfqs',
                                    'label' => 'RFQs',
                                    'route' => 'admin.procurement.desk',
                                    'route_params' => ['view' => 'rfqs'],
                                    'permission' => 'procurement.rfq.view|procurement.vendors.view',
                                    'active_routes' => [
                                        'admin.procurement.rfqs.*',
                                        'admin.procurement.vendor-comparison.*',
                                        'admin.procurement.quotations.*',
                                    ],
                                ],
                                [
                                    'key' => 'orders',
                                    'label' => 'Orders',
                                    'route' => 'admin.procurement.desk',
                                    'route_params' => ['view' => 'orders'],
                                    'permission' => 'procurement.orders.view|procurement.vendors.view',
                                    'active_routes' => ['admin.procurement.orders.*'],
                                ],
                                [
                                    'key' => 'receipts',
                                    'label' => 'Receipts',
                                    'route' => 'admin.procurement.desk',
                                    'route_params' => ['view' => 'receipts'],
                                    'permission' => 'procurement.orders.view|procurement.vendors.view',
                                    'active_routes' => ['admin.procurement.receipts.*'],
                                ],
                                [
                                    'key' => 'approvals',
                                    'label' => 'Approvals',
                                    'route' => 'admin.procurement.desk',
                                    'route_params' => ['view' => 'approvals'],
                                    'permission' => 'procurement.approvals.view',
                                    'active_routes' => ['admin.procurement.approvals.*'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'inventory-control' => [
            'title' => 'Inventory Control',
            'description' => 'Physical stock verification, cycle counting, and reconciliation.',
            'icon' => 'clipboard-list',
            'groups' => [
                [
                    'label' => 'Counts',
                    'items' => [
                        ['label' => 'Stock Count', 'description' => 'Full or partial physical stock counts.', 'route' => 'admin.inventory.stock-counts.index', 'permission' => 'inventory.count.view', 'active_routes' => ['admin.inventory.stock-counts.*'], 'icon' => 'clipboard-list'],
                        ['label' => 'Cycle Count', 'description' => 'Rolling cycle count schedules and execution.', 'route' => 'admin.inventory.cycle-counts.index', 'permission' => 'inventory.cycle.view', 'active_routes' => ['admin.inventory.cycle-counts.*'], 'icon' => 'calendar'],
                        ['label' => 'Variance Report', 'description' => 'Count variances by warehouse and item.', 'route' => 'admin.inventory.variances.index', 'permission' => 'inventory.variance.view', 'active_routes' => ['admin.inventory.variances.*'], 'icon' => 'chart-bar'],
                        ['label' => 'Inventory Reconciliation', 'description' => 'Reconcile system stock with physical counts.', 'route' => 'admin.inventory.reconciliations.index', 'permission' => 'inventory.reconcile.view', 'active_routes' => ['admin.inventory.reconciliations.*'], 'icon' => 'scale'],
                    ],
                ],
                [
                    'label' => 'Setup',
                    'items' => [
                        ['label' => 'Variance Reason Codes', 'description' => 'Structured variance reasons for stock count reconciliation.', 'route' => 'admin.inventory.variance-reason-codes.index', 'permission' => 'inventory.variance-reasons.view', 'active_routes' => ['admin.inventory.variance-reason-codes.*'], 'icon' => 'tag'],
                    ],
                ],
            ],
        ],

        'costing' => [
            'title' => 'Costing',
            'description' => 'Inventory valuation and production job costs.',
            'icon' => 'currency-dollar',
            'groups' => [
                [
                    'label' => 'Costing',
                    'items' => [
                        ['label' => 'Inventory Valuation', 'description' => 'FIFO and weighted-average inventory value by warehouse.', 'route' => 'admin.inventory.valuation.index', 'permission' => 'inventory.valuation.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.inventory.valuation.*']],
                        ['label' => 'Production Job Costing', 'description' => 'Job cost sheets and margin analysis.', 'route' => 'admin.production.costing.dashboard', 'permission' => 'production.costing.view', 'icon' => 'cog', 'active_routes' => ['admin.production.costing.*', 'admin.production.job-cards.costing']],
                    ],
                ],
            ],
        ],

    ],

];
