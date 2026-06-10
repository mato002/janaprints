<?php

return [
    'windows' => [7, 30, 90],

    'dead_stock_days' => 60,

    'critical_days_to_depletion' => 3,
    'high_days_to_depletion' => 7,
    'medium_days_to_depletion' => 14,

    'new_item_grace_days' => 14,

    'fast_moving_daily_threshold' => 5.0,
    'slow_moving_daily_threshold' => 0.1,

    'default_reorder_cover_days' => 30,

    'default_snapshot_window' => 30,

    'velocity_alert_type' => 'velocity_stockout_risk',
    'reorder_alert_type' => 'reorder_level',
];
