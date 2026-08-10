<?php

namespace App\Support\Artwork;

/**
 * Canonical query views for the Designer Desk queue.
 */
final class DesignerDeskViews
{
    public const QUEUE = 'queue';

    public const AVAILABLE = 'available';

    public const MINE = 'mine';

    public const WORKING = 'working';

    public const REVIEW = 'review';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::QUEUE,
            self::AVAILABLE,
            self::MINE,
            self::WORKING,
            self::REVIEW,
        ];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        if ($view === 'all') {
            return self::QUEUE;
        }

        return in_array($view, self::all(), true) ? $view : self::QUEUE;
    }

    public static function filterKey(?string $view): ?string
    {
        $view = self::normalize($view);

        return $view === self::QUEUE ? null : $view;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public static function deskUrl(string $view = self::QUEUE, array $query = []): string
    {
        $view = self::normalize($view);
        $filter = self::filterKey($view);

        if ($filter === null) {
            unset($query['filter']);

            return route('admin.artwork.desk', $query);
        }

        return route('admin.artwork.desk', array_merge($query, ['filter' => $filter]));
    }

    public static function availableUrl(array $query = []): string
    {
        return self::deskUrl(self::AVAILABLE, $query);
    }

    public static function mineUrl(array $query = []): string
    {
        return self::deskUrl(self::MINE, $query);
    }

    public static function workingUrl(array $query = []): string
    {
        return self::deskUrl(self::WORKING, $query);
    }

    public static function reviewUrl(array $query = []): string
    {
        return self::deskUrl(self::REVIEW, $query);
    }
}
