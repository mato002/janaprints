<?php

namespace App\Enums;

enum SystemHealthStatus: string
{
    case Healthy = 'healthy';
    case Warning = 'warning';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => __('Healthy'),
            self::Warning => __('Warning'),
            self::Critical => __('Critical'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Healthy => 'success',
            self::Warning => 'warning',
            self::Critical => 'danger',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::Healthy => 0,
            self::Warning => 1,
            self::Critical => 2,
        };
    }

    public function isWorseThan(self $other): bool
    {
        return $this->rank() > $other->rank();
    }

    public static function worst(SystemHealthStatus ...$statuses): self
    {
        $worst = self::Healthy;

        foreach ($statuses as $status) {
            if ($status->isWorseThan($worst)) {
                $worst = $status;
            }
        }

        return $worst;
    }
}
