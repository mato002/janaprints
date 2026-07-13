<?php

return [
    'default_margin_percent' => 35.0,

    'electricity_rate_per_kwh' => 25.0,

    'labour_hourly_rate' => 500.0,

    'default_estimation_confidence' => 0.0,

    'supported_print_methods' => [
        'digital',
        'offset',
        'large_format',
        'screen',
        'dtf',
        'dye_sub',
        'uv',
        'latex',
        'ecosolvent',
    ],

    'supported_ink_types' => [
        'cmyk',
        'black',
        'ecosolvent',
        'uv',
        'latex',
        'dtf',
        'dye_sub',
    ],

    'future_artwork_analysis_enabled' => false,

    'future_ai_analysis_enabled' => false,

    'future_estimate_learning_enabled' => false,

    'artwork_analysis_enabled' => true,

    'allowed_artwork_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'webp'],

    'allowed_artwork_mimes' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/tiff',
        'image/webp',
    ],

    'max_artwork_upload_mb' => 50,

    'storage_disk' => env('PRINTING_INTELLIGENCE_DISK', 'local'),

    'pdf_metadata_tool_enabled' => true,

    'image_metadata_enabled' => true,

    'future_colour_analysis_enabled' => false,

    'colour_analysis_enabled' => true,

    'coverage_white_threshold_rgb' => 245,

    'coverage_low_percent' => 20,

    'coverage_medium_percent' => 50,

    'coverage_high_percent' => 80,

    'heavy_coverage_warning_percent' => 80,

    'black_heavy_warning_percent' => 70,

    'low_resolution_dpi_threshold' => 150,

    'pixel_sampling_max_pixels' => 500000,

    'dominant_colours_min_percent' => 0.1,

    'dominant_colours_max_count' => 0,

    'colour_bucket_divisor' => 32,

    'ghostscript_enabled' => true,

    'ghostscript_binary' => env('GHOSTSCRIPT_BINARY', 'gs'),

    'future_ink_costing_enabled' => false,

    'future_ai_colour_feedback_enabled' => false,

    'ink_costing_enabled' => true,

    'default_formula_version' => 'PI3-V1',

    'default_cmyk_coverage_factor' => 1.0,

    'minimum_confidence_score' => 40.0,

    'high_confidence_score' => 80.0,

    'allow_estimation_without_yield' => false,

    'default_ml_per_sq_m_fallback' => 0.5,

    'future_real_machine_calibration_enabled' => false,

    'production_costing_enabled' => true,

    'production_formula_version' => 'PI4-V1',

    'default_minimum_run_hours' => 0.25,

    'ink_run_time_factor' => 0.15,

    'default_overhead_percent' => 10.0,

    'machine_selection_prefer_primary' => true,

    'future_quotation_costing_enabled' => false,

    'quotation_estimation_enabled' => true,

    'quotation_formula_version' => 'PI5-V1',

    'default_minimum_margin_percent' => 20.0,

    'default_target_margin_percent' => 35.0,

    'default_wastage_percent' => 5.0,

    'default_labour_buffer_percent' => 0.0,

    'rounding_rule' => 'nearest_10',

    'allow_apply_to_quotation' => true,

    'require_confirmation_to_apply' => true,

    'future_auto_quote_enabled' => false,

    'future_ai_quote_advisor_enabled' => false,

    'estimate_actual_learning_enabled' => true,

    'estimate_actual_formula_version' => 'PI6-V1',

    'calibration_recommendation_enabled' => true,

    'calibration_formula_version' => 'PI7-V1',

    'calibration_ink_variance_trigger_percent' => 15.0,

    'calibration_machine_variance_trigger_percent' => 10.0,

    'calibration_min_sample_size' => 20,

    'profitability_intelligence_enabled' => true,

    'profitability_formula_version' => 'PI8-V1',

    'product_type_labels' => [
        'business_cards' => 'Business Cards',
        'banners' => 'Banners',
        'posters' => 'Posters',
        'flyers' => 'Flyers',
        'stickers' => 'Stickers',
        'large_format' => 'Large Format',
        'digital_print' => 'Digital Print',
        'offset' => 'Offset',
        'unknown' => 'Unknown',
    ],

    'material_aliases' => [
        'paper_a4' => ['paper', 'a4', 'bond', 'copy paper'],
        'banner' => ['banner', 'flex', 'pvc banner'],
        'vinyl' => ['vinyl', 'adhesive vinyl'],
        'sticker' => ['sticker', 'label', 'decals'],
        'correx' => ['correx', 'corrugated plastic', 'coroplast'],
        'canvas' => ['canvas', 'fabric print'],
    ],

    'executive_forecasting_enabled' => true,

    'forecast_formula_version' => 'PI9-V1',

    'default_forecast_model' => 'weighted_average',

    'forecast_history_months' => 12,

    'forecast_min_history_periods' => 3,

    'capacity_bottleneck_threshold' => 35,

    'capacity_underutilized_threshold' => 5,

    'customer_concentration_risk_threshold' => 60,

    'margin_erosion_alert_threshold' => 15,

    'advisor_enabled' => true,

    'advisor_formula_version' => 'PI10-V1',

    'async_analysis_enabled' => env('PRINTING_INTELLIGENCE_ASYNC', true),
];
