<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public hash format
    |--------------------------------------------------------------------------
    |
    | Plain base62 tokens only — no entity prefixes (no INV_, QT_, JOB_, etc.).
    |
    */

    'length' => 16,

    'charset' => 'base62',

    'column' => 'public_id',

    /*
    |--------------------------------------------------------------------------
    | Numeric fallback (migration window)
    |--------------------------------------------------------------------------
    |
    | While legacy bookmarks and emails still use numeric IDs, binding may resolve
    | by primary key when the value is all digits. Disable after rollout completes.
    |
    */

    'numeric_fallback_enabled' => env('PUBLIC_HASH_NUMERIC_FALLBACK', false),

    'fallback_log_enabled' => true,

    'fallback_redirect_enabled' => false,

    /**
     * Route-specific legacy numeric resolution when global fallback is disabled.
     * Valid signed public receipt URLs may still use numeric payment IDs during TTL window.
     */
    'signed_receipt_legacy_numeric_enabled' => env('PUBLIC_HASH_SIGNED_RECEIPT_LEGACY_NUMERIC', true),

    'max_generation_attempts' => 5,

    /*
    |--------------------------------------------------------------------------
    | External surface route prefixes (client portal, signed public links)
    |--------------------------------------------------------------------------
    */

    'external_route_prefixes' => [
        'client/quotations/',
        'client/orders/',
        'client/jobs/',
        'client/invoices/',
        'client/artwork/',
        'payment-receipt/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route-exposed models (rollout registry)
    |--------------------------------------------------------------------------
    |
    | Populated incrementally in PUBLIC-HASH-02+. The audit and backfill commands
    | use this list with --all. Individual models can always be targeted via
    | --model=Fully\Qualified\ModelClass.
    |
    */

    'route_exposed_models' => [
        // Tier 1 — core commercial entities (PUBLIC-HASH-02)
        \App\Models\Crm\Customer::class,
        \App\Models\Crm\Lead::class,
        \App\Models\Sales\Quotation::class,
        \App\Models\Sales\SalesOrder::class,
        \App\Models\Production\ProductionJobCard::class,
        \App\Models\Sales\CustomerInvoice::class,
        \App\Models\Sales\CustomerPayment::class,

        // Tier 2 — production, dispatch, artwork & inventory (PUBLIC-HASH-03)
        \App\Models\Production\ProductionSpecification::class,
        \App\Models\Production\PrintProductTemplate::class,
        \App\Models\Production\ProductionQueue::class,
        \App\Models\Production\WorkCenter::class,
        \App\Models\Production\QualityCheck::class,
        \App\Models\Artwork\ArtworkRequest::class,
        \App\Models\Artwork\ArtworkFile::class,
        \App\Models\Artwork\ArtworkVersion::class,
        \App\Models\Dispatch\DeliveryNote::class,
        \App\Models\Inventory\InventoryItem::class,
        \App\Models\Inventory\Warehouse::class,
        \App\Models\Inventory\StockReceipt::class,
        \App\Models\Inventory\StockIssue::class,
        \App\Models\Inventory\StockAdjustment::class,
        \App\Models\Assets\FixedAsset::class,
        \App\Models\Assets\MaintenanceWorkOrder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Deferred models (not yet on public hash routes)
    |--------------------------------------------------------------------------
    */

    'deferred_models' => [
        \App\Models\Crm\CustomerArtwork::class => 'Client artwork library routes',
        \App\Models\Hr\PayrollPayslip::class => 'ESS payslip download',
        \App\Models\Hr\EmployeeDocument::class => 'ESS document download',
        \App\Models\Communications\ErpNotification::class => 'Admin notification center bind',
        \App\Models\Communications\SmsCampaign::class => 'SMS campaign routes',
        \App\Models\Communications\Inbox\CommunicationConversation::class => 'Inbox conversation routes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit allowlists (deferred / non-migrated bindings and views)
    |--------------------------------------------------------------------------
    */

    'deferred_route_bindings' => [
        'notification',
        'campaign',
        'communicationLog',
        'conversation',
        'message',
        'emailCampaign',
        'emailMessage',
        'inboxConversation',
        'printSpecification',
    ],

    'deferred_view_paths' => [
        'resources/views/admin/printing-intelligence/operations-advisor.blade.php',
    ],

];
