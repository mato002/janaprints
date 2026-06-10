<?php

/**
 * Jana Prints inventory lifecycle architecture (Phase I4.1).
 *
 * Inventory subledger (quantity truth = inventory_movements only):
 *   Raw Materials (physical) → Finished Goods (virtual) → In Transit (virtual) → Delivered (removed)
 *
 * Accounting subledger (general ledger):
 *   Raw Materials → WIP → Finished Goods → COGS
 *
 * WIP is an accounting layer only. It is NOT a physical inventory layer.
 * WIP virtual warehouse records are reserved for a future partial-production design
 * and must not receive production movements in the current architecture.
 */
return [
    'inventory_stages' => [
        'raw_materials',
        'finished_goods',
        'in_transit',
        'delivered',
    ],

    'accounting_stages' => [
        'raw_materials',
        'wip',
        'finished_goods',
        'cogs',
    ],

    'wip' => [
        'accounting_only' => true,
        'virtual_warehouse_active' => false,
        'wip_posting_source' => 'production_material_consumption',
        'stock_issue_production_posts_wip' => false,
    ],

    'production_completion' => [
        'one_posted_output_per_job' => true,
    ],

    'dispatch' => [
        'cancel_allowed_statuses' => ['draft'],
    ],
];
