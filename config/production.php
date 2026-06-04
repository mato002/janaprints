<?php

/**
 * Production module defaults (no schedule tables — planning heuristics only).
 */
return [

    /**
     * Maps work center codes to production stage codes (planning visibility only).
     *
     * @var array<string, string>
     */
    'work_center_stage_codes' => [
        'DESIGN' => 'PENDING',
        'PREPRESS' => 'PREPRESS',
        'DIGITAL' => 'PRINTING',
        'OFFSET' => 'PRINTING',
        'LARGE_FORMAT' => 'PRINTING',
        'FINISHING' => 'FINISHING',
        'PACKAGING' => 'DISPATCH',
    ],

    'scheduling' => [
        /** Max concurrent assigned jobs per work center before overbooked. */
        'default_work_center_capacity' => (int) env('PRODUCTION_WORK_CENTER_CAPACITY', 5),
        'planning_window_days' => 14,
    ],

];
