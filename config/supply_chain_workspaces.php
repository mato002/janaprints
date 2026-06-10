<?php

/**
 * Supply Chain workspace hub and section catalogs (presentation only).
 * Root hub shows seven workspace cards; features live on section pages.
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
            'label' => 'Store Operations',
            'description' => 'Warehouses, balances, transfers, adjustments, and stock movements.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'store-operations'],
            'permission' => 'inventory.view',
            'icon' => 'building',
            'active_routes' => ['admin.workspaces.supply-chain.section:store-operations', 'admin.inventory.store.*', 'admin.inventory.warehouses.*', 'admin.inventory.transfers.*', 'admin.inventory.adjustments.*', 'admin.inventory.movements.*', 'admin.inventory.receipts.*', 'admin.inventory.issues.*'],
        ],
        [
            'label' => 'Procurement',
            'description' => 'Suppliers, RFQs, purchase orders, goods receipts, and vendor analysis.',
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
                'admin.inventory.intelligence.*',
            ],
        ],
        [
            'label' => 'Costing',
            'description' => 'FIFO, average cost, inventory valuation, and production job costing.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'costing'],
            'permission' => 'inventory.valuation.view|production.costing.view',
            'icon' => 'currency-dollar',
            'active_routes' => ['admin.workspaces.supply-chain.section:costing', 'admin.inventory.valuation.*', 'admin.production.costing.*'],
        ],
        [
            'label' => 'Assets',
            'description' => 'Asset register, categories, maintenance, depreciation, and disposals.',
            'route' => 'admin.workspaces.assets',
            'permission' => 'assets.view',
            'icon' => 'chip',
            'active_routes' => ['admin.workspaces.assets', 'admin.assets.*'],
        ],
        [
            'label' => 'Reports',
            'description' => 'Inventory, procurement, valuation, movement, and costing reports.',
            'route' => 'admin.workspaces.supply-chain.section',
            'route_params' => ['section' => 'reports'],
            'permission' => 'inventory.view|procurement.vendors.view',
            'icon' => 'chart-pie',
            'active_routes' => ['admin.workspaces.supply-chain.section:reports'],
        ],
    ],

    'sections' => [

        'catalogue' => [
            'title' => 'Catalogue',
            'description' => 'Product master data, classification, attributes, and commercial pricing.',
            'icon' => 'template',
            'groups' => [
                [
                    'label' => 'Catalogue',
                    'items' => [
                        ['key' => 'products', 'label' => 'Products', 'description' => 'Finished goods, materials, and sellable inventory items.', 'route' => 'admin.inventory.items.index', 'permission' => 'catalogue.view', 'icon' => 'template', 'active_routes' => ['admin.inventory.items.*']],
                        ['key' => 'categories', 'label' => 'Categories', 'description' => 'Print-industry category master data.', 'route' => 'admin.inventory.catalogue.categories.index', 'permission' => 'catalogue.view', 'icon' => 'folder', 'active_routes' => ['admin.inventory.catalogue.categories.*']],
                        ['key' => 'subcategories', 'label' => 'Subcategories', 'description' => 'Category-specific product classification.', 'route' => 'admin.inventory.catalogue.subcategories.index', 'permission' => 'catalogue.view', 'icon' => 'archive', 'active_routes' => ['admin.inventory.catalogue.subcategories.*']],
                        ['key' => 'brands', 'label' => 'Brands', 'description' => 'Supplier and manufacturer brand master data.', 'route' => 'admin.inventory.catalogue.brands.index', 'permission' => 'catalogue.view', 'icon' => 'badge-check', 'active_routes' => ['admin.inventory.catalogue.brands.*']],
                        ['key' => 'attributes', 'label' => 'Attributes', 'description' => 'Reusable GSM, size, finish, color, and material fields.', 'route' => 'admin.inventory.catalogue.attributes.index', 'permission' => 'catalogue.view', 'icon' => 'sliders', 'active_routes' => ['admin.inventory.catalogue.attributes.*']],
                        ['key' => 'price-lists', 'label' => 'Price Lists', 'description' => 'Retail, wholesale, corporate, and government pricing.', 'route' => 'admin.inventory.catalogue.price-lists.index', 'permission' => 'catalogue.view', 'icon' => 'tag', 'active_routes' => ['admin.inventory.catalogue.price-lists.*']],
                    ],
                ],
            ],
        ],

        'store-operations' => [
            'title' => 'Store Operations',
            'description' => 'Warehouse structure, stock balances, transfers, adjustments, and movement history.',
            'icon' => 'building',
            'groups' => [
                [
                    'label' => 'Store Operations',
                    'items' => [
                        ['label' => 'Warehouses', 'description' => 'Branch stores, warehouse details, managers, and balances.', 'route' => 'admin.inventory.warehouses.index', 'permission' => 'inventory.view', 'icon' => 'building', 'active_routes' => ['admin.inventory.warehouses.*']],
                        ['label' => 'Store Balances', 'description' => 'Stock balances by warehouse and item.', 'route' => 'admin.inventory.store.balances', 'permission' => 'inventory.view', 'icon' => 'cube', 'active_routes' => ['admin.inventory.store.balances']],
                        ['label' => 'Goods Receiving', 'description' => 'Inbound stock receipts and goods receiving notes.', 'route' => 'admin.inventory.receipts.index', 'permission' => 'inventory.view', 'icon' => 'archive', 'active_routes' => ['admin.inventory.receipts.*']],
                        ['label' => 'Stock Issues', 'description' => 'Outbound stock issues to production, sales, and transfers.', 'route' => 'admin.inventory.issues.index', 'permission' => 'inventory.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.inventory.issues.*']],
                        ['label' => 'Transfers', 'description' => 'Inter-store transfer drafts and posted stock movements.', 'route' => 'admin.inventory.transfers.index', 'permission' => 'inventory.view', 'icon' => 'truck', 'active_routes' => ['admin.inventory.transfers.*']],
                        ['label' => 'Adjustments', 'description' => 'Stock count corrections and write-offs.', 'route' => 'admin.inventory.adjustments.index', 'permission' => 'inventory.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.inventory.adjustments.*']],
                        ['label' => 'Stock Movements', 'description' => 'Material usage and movement history.', 'route' => 'admin.inventory.movements.index', 'permission' => 'inventory.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.inventory.movements.*']],
                        ['label' => 'Virtual Locations', 'description' => 'Raw, WIP, finished goods, in-transit, and quarantine buckets.', 'route' => 'admin.inventory.virtual-locations.index', 'permission' => 'inventory.virtual-locations.view', 'active_routes' => ['admin.inventory.virtual-locations.*'], 'icon' => 'collection'],
                    ],
                ],
            ],
        ],

        'procurement' => [
            'title' => 'Procurement',
            'description' => 'Supplier master data, sourcing, purchasing, and inbound logistics.',
            'icon' => 'truck',
            'groups' => [
                [
                    'label' => 'Procurement',
                    'items' => [
                        ['label' => 'Suppliers', 'description' => 'Supplier master data and contacts.', 'route' => 'admin.procurement.vendors.index', 'permission' => 'procurement.vendors.view', 'icon' => 'office-building', 'active_routes' => ['admin.procurement.vendors.*']],
                        ['label' => 'RFQs', 'description' => 'Request for quotation and vendor responses.', 'route' => 'admin.procurement.rfqs.index', 'permission' => 'procurement.rfq.view|procurement.vendors.view', 'icon' => 'document-text', 'active_routes' => ['admin.procurement.rfqs.*', 'admin.procurement.requests.rfq.*']],
                        ['label' => 'Vendor Comparison', 'description' => 'Compare supplier quotes and award RFQs.', 'route' => 'admin.procurement.vendor-comparison.index', 'permission' => 'procurement.vendor_comparison.view|procurement.comparison.view', 'icon' => 'scale', 'active_routes' => ['admin.procurement.vendor-comparison.*']],
                        ['label' => 'Purchase Orders', 'description' => 'Approved purchase orders and fulfilment.', 'route' => 'admin.procurement.orders.index', 'permission' => 'procurement.orders.view|procurement.vendors.view', 'icon' => 'clipboard-list', 'active_routes' => ['admin.procurement.orders.*']],
                        ['label' => 'Goods Receipts', 'description' => 'Procurement goods receipt notes and posting.', 'route' => 'admin.procurement.receipts.index', 'permission' => 'procurement.orders.view|procurement.vendors.view', 'icon' => 'archive', 'active_routes' => ['admin.procurement.receipts.*', 'admin.procurement.orders.receive.*']],
                        ['label' => 'Asset Capitalization', 'description' => 'Pending fixed-asset capitalization from receipts.', 'route' => 'admin.assets.acquisitions.queue', 'permission' => 'assets.acquisition.view', 'icon' => 'chip', 'count_key' => 'capitalization_pending', 'active_routes' => ['admin.assets.acquisitions.queue', 'admin.assets.acquisitions.workbench']],
                        ['label' => 'Supplier Performance', 'description' => 'On-time delivery, quality, and spend analytics.', 'route' => 'admin.procurement.supplier-performance.index', 'permission' => 'procurement.performance.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.procurement.supplier-performance.*']],
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
                    'label' => 'Inventory Control',
                    'items' => [
                        ['label' => 'Stock Count', 'description' => 'Full or partial physical stock counts.', 'route' => 'admin.inventory.stock-counts.index', 'permission' => 'inventory.count.view', 'active_routes' => ['admin.inventory.stock-counts.*'], 'icon' => 'clipboard-list'],
                        ['label' => 'Cycle Count', 'description' => 'Rolling cycle count schedules and execution.', 'route' => 'admin.inventory.cycle-counts.index', 'permission' => 'inventory.cycle.view', 'active_routes' => ['admin.inventory.cycle-counts.*'], 'icon' => 'calendar'],
                        ['label' => 'Variance Report', 'description' => 'Count variances by warehouse and item.', 'route' => 'admin.inventory.variances.index', 'permission' => 'inventory.variance.view', 'active_routes' => ['admin.inventory.variances.*'], 'icon' => 'chart-bar'],
                        ['label' => 'Inventory Reconciliation', 'description' => 'Reconcile system stock with physical counts.', 'route' => 'admin.inventory.reconciliations.index', 'permission' => 'inventory.reconcile.view', 'active_routes' => ['admin.inventory.reconciliations.*'], 'icon' => 'scale'],
                        ['label' => 'Variance Reason Codes', 'description' => 'Structured variance reasons for stock count reconciliation.', 'route' => 'admin.inventory.variance-reason-codes.index', 'permission' => 'inventory.variance-reasons.view', 'active_routes' => ['admin.inventory.variance-reason-codes.*'], 'icon' => 'tag'],
                        ['label' => 'Inventory Intelligence', 'description' => 'Velocity, stockout risk, dead stock, and warehouse consumption insights.', 'route' => 'admin.inventory.intelligence.overview', 'permission' => 'inventory.intelligence.view', 'active_routes' => ['admin.inventory.intelligence.*'], 'icon' => 'chart-bar'],
                    ],
                ],
            ],
        ],

        'costing' => [
            'title' => 'Costing',
            'description' => 'Inventory costing methods, valuation, and production job costs.',
            'icon' => 'currency-dollar',
            'groups' => [
                [
                    'label' => 'Costing',
                    'items' => [
                        ['label' => 'FIFO Costing', 'description' => 'First-in, first-out layer consumption and value.', 'route' => 'admin.inventory.valuation.index', 'permission' => 'inventory.valuation.view', 'icon' => 'collection', 'active_routes' => ['admin.inventory.valuation.*']],
                        ['label' => 'Average Cost', 'description' => 'Weighted-average inventory unit costs.', 'coming_soon' => true, 'icon' => 'chart-pie'],
                        ['label' => 'Inventory Valuation', 'description' => 'FIFO and weighted-average inventory value.', 'route' => 'admin.inventory.valuation.index', 'permission' => 'inventory.valuation.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.inventory.valuation.*']],
                        ['label' => 'Production Job Costing', 'description' => 'Job cost sheets and margin analysis.', 'route' => 'admin.production.costing.dashboard', 'permission' => 'production.costing.view', 'icon' => 'cog', 'active_routes' => ['admin.production.costing.*', 'admin.production.job-cards.costing']],
                    ],
                ],
            ],
        ],

        'assets' => [
            'title' => 'Assets',
            'description' => 'Fixed assets are managed in the Assets workspace.',
            'icon' => 'chip',
            'groups' => [
                [
                    'label' => 'Assets',
                    'items' => [
                        ['label' => 'Assets Workspace', 'description' => 'Canonical fixed asset register, maintenance, finance, and intelligence.', 'route' => 'admin.workspaces.assets', 'permission' => 'assets.view', 'icon' => 'chip', 'active_routes' => ['admin.workspaces.assets', 'admin.assets.*']],
                    ],
                ],
            ],
        ],

        'reports' => [
            'title' => 'Reports',
            'description' => 'Supply chain analytics across inventory, procurement, valuation, and costing.',
            'icon' => 'chart-pie',
            'groups' => [
                [
                    'label' => 'Reports',
                    'items' => [
                        ['label' => 'Inventory Reports', 'description' => 'Stock movement and on-hand analytics.', 'route' => 'admin.inventory.reports.index', 'permission' => 'reports.inventory.view', 'icon' => 'cube', 'active_routes' => ['admin.inventory.reports.*']],
                        ['label' => 'Procurement Reports', 'description' => 'Purchasing and supplier performance.', 'route' => 'admin.procurement.reports.index', 'permission' => 'reports.procurement.view', 'icon' => 'truck', 'active_routes' => ['admin.procurement.reports.*']],
                        ['label' => 'Valuation Reports', 'description' => 'Inventory value by method and warehouse.', 'route' => 'admin.inventory.valuation.index', 'permission' => 'inventory.valuation.view', 'icon' => 'currency-dollar', 'active_routes' => ['admin.inventory.valuation.*']],
                        ['label' => 'Movement Reports', 'description' => 'Stock movement history and consumption.', 'route' => 'admin.inventory.movements.index', 'permission' => 'inventory.view', 'icon' => 'switch-horizontal', 'active_routes' => ['admin.inventory.movements.*']],
                        ['label' => 'Costing Reports', 'description' => 'Cost layers, margins, and job profitability.', 'route' => 'admin.production.reports.index', 'permission' => 'reports.costing.view', 'icon' => 'chart-bar', 'active_routes' => ['admin.production.reports.*']],
                    ],
                ],
            ],
        ],

    ],

];
