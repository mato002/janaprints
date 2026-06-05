<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cash variance tolerance (KES)
    |--------------------------------------------------------------------------
    | When |actual - expected| cash exceeds this amount at session close,
    | the session enters pending_approval until a manager approves.
    */
    'cash_variance_tolerance' => (float) env('POS_CASH_VARIANCE_TOLERANCE', 100),

    /*
    |--------------------------------------------------------------------------
    | Default terminal label when none is specified at session open.
    |--------------------------------------------------------------------------
    */
    'default_terminal' => env('POS_DEFAULT_TERMINAL', 'Counter 1'),
];
