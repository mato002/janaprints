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
        'default_job_duration_days' => (int) env('PRODUCTION_DEFAULT_JOB_DURATION_DAYS', 2),
    ],

    /**
     * Maps production types to primary work center codes for legacy auto-scheduling.
     *
     * @var array<string, string|list<string>>
     */
    'production_type_work_center_codes' => [
        'digital' => 'DIGITAL',
        'offset' => 'OFFSET',
        'large_format' => 'LARGE_FORMAT',
        'finishing' => 'FINISHING',
        'packaging' => 'PACKAGING',
        'mixed' => 'DESIGN',
    ],

    /**
     * Department queue workspaces (slug → work center codes / job filters).
     * Only departments with matching active work centers are exposed in the UI.
     *
     * @var array<string, array{label: string, work_center_codes?: list<string>, production_types?: list<string>, job_statuses?: list<string>}>
     */
    'departments' => [
        'digital' => [
            'label' => 'Digital',
            'work_center_codes' => ['DIGITAL'],
            'production_types' => ['digital'],
        ],
        'offset' => [
            'label' => 'Offset',
            'work_center_codes' => ['OFFSET'],
            'production_types' => ['offset'],
        ],
        'large_format' => [
            'label' => 'Large Format',
            'work_center_codes' => ['LARGE_FORMAT'],
            'production_types' => ['large_format'],
        ],
        'finishing' => [
            'label' => 'Finishing',
            'work_center_codes' => ['FINISHING'],
            'production_types' => ['finishing'],
        ],
        'packaging' => [
            'label' => 'Packaging',
            'work_center_codes' => ['PACKAGING'],
            'production_types' => ['packaging'],
        ],
        'outsource' => [
            'label' => 'Outsource',
            'job_statuses' => ['outsourced'],
        ],
    ],

    /** Days in queue before highlighting as long-waiting. */
    'queue_waiting_alert_days' => (int) env('PRODUCTION_QUEUE_WAITING_ALERT_DAYS', 3),
];
