<?php

namespace App\Support\Crm;

/**
 * Canonical query views for the consolidated CRM Desk (customers index).
 */
final class CrmDeskViews
{
    public const CUSTOMERS = 'customers';

    public const LEADS = 'leads';

    public const ACTIVITIES = 'activities';

    public const SEGMENTS = 'segments';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::CUSTOMERS, self::LEADS, self::ACTIVITIES, self::SEGMENTS];
    }

    public static function normalize(?string $view): string
    {
        $view = is_string($view) ? trim($view) : '';

        return in_array($view, self::all(), true) ? $view : self::CUSTOMERS;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function deskQuery(string $view = self::CUSTOMERS, array $query = []): array
    {
        unset($query['view']);

        $params = array_merge($query, ['view' => self::normalize($view)]);

        if (($params['view'] ?? self::CUSTOMERS) === self::CUSTOMERS) {
            unset($params['view']);
        }

        return array_filter(
            $params,
            fn ($value) => $value !== null && $value !== '',
        );
    }

    public static function deskUrl(string $view = self::CUSTOMERS, array $query = []): string
    {
        return route('admin.crm.customers.index', self::deskQuery($view, $query));
    }

    public static function customersUrl(array $query = []): string
    {
        return self::deskUrl(self::CUSTOMERS, $query);
    }

    public static function leadsUrl(array $query = []): string
    {
        return self::deskUrl(self::LEADS, $query);
    }

    public static function activitiesUrl(array $query = []): string
    {
        return self::deskUrl(self::ACTIVITIES, $query);
    }

    public static function segmentsUrl(array $query = []): string
    {
        return self::deskUrl(self::SEGMENTS, $query);
    }
}
