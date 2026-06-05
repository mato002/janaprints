<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vendor Comparison Scoring Weights
    |--------------------------------------------------------------------------
    |
    | Weights must sum to 100. Procurement managers with manage permission
    | can override per comparison workspace session.
    |
    */

    'weights' => [
        'price' => 40,
        'performance' => 25,
        'lead_time' => 20,
        'quality' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reliability sub-weight (within performance dimension)
    |--------------------------------------------------------------------------
    */

    'reliability_blend' => 0.5,

];
