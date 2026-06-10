<?php

use App\Enums\ActivityStatus;
use App\Enums\CommercialPriceBookStatus;
use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;

return [

    /*
    |--------------------------------------------------------------------------
    | Default status enums per form registry key
    |--------------------------------------------------------------------------
    |
    | When a tenant first opens form settings or a status dropdown, system
    | options are seeded from these enums. Tenants may add more statuses later.
    |
    */

    'defaults' => [
        'customer' => CustomerStatus::class,
        'lead' => LeadStatus::class,
        'activity.create' => ActivityStatus::class,
        'commercial_price_book.create' => CommercialPriceBookStatus::class,
    ],

];
