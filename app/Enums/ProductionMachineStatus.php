<?php

namespace App\Enums;

enum ProductionMachineStatus: string
{
    case Available = 'available';
    case Running = 'running';
    case Idle = 'idle';
    case Offline = 'offline';
    case Maintenance = 'maintenance';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Available => __('Available'),
            self::Running => __('Running'),
            self::Idle => __('Idle'),
            self::Offline => __('Offline'),
            self::Maintenance => __('Maintenance'),
            self::Retired => __('Retired'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Running => 'success',
            self::Idle => 'warning',
            self::Offline => 'neutral',
            self::Maintenance => 'warning',
            self::Retired => 'neutral',
        };
    }

    public function acceptsJobs(): bool
    {
        return in_array($this, [self::Available, self::Running, self::Idle], true);
    }
}
