<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Employee number format
    |--------------------------------------------------------------------------
    |
    | Prefix identifies Jana Prints staff in HR records and payslips.
    | Example with prefix "JPEMP": JPEMP-0766
    |
    | Set EMPLOYEE_NUMBER_PREFIX in .env to override. When empty and
    | use_company_code is true, the active company code is used instead.
    |
    */

    'employee_number' => [
        'prefix' => env('EMPLOYEE_NUMBER_PREFIX', 'JPEMP'),
        'use_company_code' => filter_var(env('EMPLOYEE_NUMBER_USE_COMPANY_CODE', false), FILTER_VALIDATE_BOOL),
        'sequence_padding' => (int) env('EMPLOYEE_NUMBER_SEQUENCE_PADDING', 4),
    ],

];
