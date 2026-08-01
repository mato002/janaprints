<?php

namespace App\Support\Production;

/**
 * Canonical query views for the consolidated Production Floor desk.
 */
final class ProductionFloorDeskViews
{
    public const FLOOR = 'floor';

    public const REGISTER = 'register';

    public const QUEUE = 'queue';

    public const OUTPUTS = 'outputs';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::FLOOR, self::REGISTER, self::QUEUE, self::OUTPUTS];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::FLOOR;
    }

    public static function isPanelView(?string $view): bool
    {
        return self::normalize($view) !== self::FLOOR;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function floorQuery(string $view = self::FLOOR, array $query = []): array
    {
        unset($query['view'], $query['department']);

        $params = array_merge($query, ['view' => self::normalize($view)]);

        if (($params['view'] ?? self::FLOOR) === self::FLOOR) {
            unset($params['view']);
        }

        return array_filter(
            $params,
            fn ($value) => $value !== null && $value !== '',
        );
    }

    public static function floorUrl(string $view = self::FLOOR, array $query = []): string
    {
        return route('admin.production.floor', self::floorQuery($view, $query));
    }

    public static function registerIndexUrl(array $query = []): string
    {
        return self::floorUrl(self::REGISTER, $query);
    }

    public static function queueIndexUrl(?string $department = null, array $query = []): string
    {
        $params = self::floorQuery(self::QUEUE, $query);

        if (filled($department)) {
            $params['department'] = $department;
        }

        return route('admin.production.floor', $params);
    }

    public static function outputsIndexUrl(array $query = []): string
    {
        return self::floorUrl(self::OUTPUTS, $query);
    }
}
