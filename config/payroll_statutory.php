<?php

/**
 * Default Kenya payroll statutory rates and bands.
 * Company overrides are stored in system settings (see PayrollStatutorySettingsService).
 */
return [
    'nssf' => [
        'employee_rate' => 0.06,
        'employer_rate' => 0.06,
        'pensionable_ceiling' => 36000,
    ],

    'shif' => [
        'employee_rate' => 0.0275,
        'employer_rate' => 0.0,
        'minimum_contribution' => 300,
    ],

    'housing_levy' => [
        'employee_rate' => 0.015,
        'employer_rate' => 0.015,
    ],

    'paye' => [
        'personal_relief' => 2400,
        'bands' => [
            ['limit' => 24000, 'rate' => 0.10],
            ['limit' => 8333, 'rate' => 0.25],
            ['limit' => 467667, 'rate' => 0.30],
            ['limit' => PHP_FLOAT_MAX, 'rate' => 0.35],
        ],
    ],

    /**
     * Additional configurable statutory deductions applied after core statutories.
     * Each entry: code, name, rate (decimal), base (gross|taxable), side (employee|employer).
     */
    'additional' => [],
];
