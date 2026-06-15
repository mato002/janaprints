<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default ERP Role After Activation
    |--------------------------------------------------------------------------
    |
    | Used when no explicit role, job title map, or department map applies.
    | Must match an existing Spatie role name (see RolesAndPermissionsSeeder).
    |
    */

    /*
    | Fallback role chain when no explicit role, job title, or department map applies.
    | Staff is preferred when seeded; Viewer is the safe fallback when Staff is absent.
    */
    'fallback_roles' => array_values(array_filter(array_map(
        trim(...),
        explode(',', env('EMPLOYEE_DEFAULT_ROLE_FALLBACK', 'Staff,Viewer')),
    ))),

    /** @deprecated Use fallback_roles. Kept for backward compatibility only. */
    'default_role' => env('EMPLOYEE_DEFAULT_ROLE'),

    'sms' => [
        'enabled' => env('EMPLOYEE_ONBOARDING_SMS_ENABLED', true),
        'message' => env(
            'EMPLOYEE_ONBOARDING_SMS_MESSAGE',
            'Welcome to Jana Prints. Your onboarding link has been sent to your personal email. Please check your inbox.',
        ),
        'include_activation_link' => env('EMPLOYEE_ONBOARDING_SMS_INCLUDE_LINK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Title → Role Mapping
    |--------------------------------------------------------------------------
    |
    | Keys are job title codes (e.g. SALES_EXEC). Values are Spatie role names.
    |
    */

    'job_title_role_map' => [
        'SALES_EXEC' => 'Sales',
        'COM_MGR' => 'Sales',
        'DESIGNER' => 'Designer',
        'PROD_MGR' => 'Production',
        'STOREKEEPER' => 'Storekeeper',
        'ACCOUNTANT' => 'Accountant',
        'HR_OFFICER' => 'HR',
        'RECEPTIONIST' => 'Viewer',
        'CASHIER' => 'Viewer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Department → Role Mapping
    |--------------------------------------------------------------------------
    |
    | Keys are department codes (e.g. ADMIN). Values are Spatie role names.
    |
    */

    'department_role_map' => [
        'ADMIN' => 'Viewer',
    ],

];
