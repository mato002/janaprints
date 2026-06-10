<?php

namespace App\Enums;

enum ForecastModel: string
{
    case MovingAverage = 'moving_average';
    case WeightedAverage = 'weighted_average';
    case TrendProjection = 'trend_projection';

    public function label(): string
    {
        return match ($this) {
            self::MovingAverage => __('Moving average'),
            self::WeightedAverage => __('Weighted average'),
            self::TrendProjection => __('Trend projection'),
        };
    }
}
