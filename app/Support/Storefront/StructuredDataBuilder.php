<?php

namespace App\Support\Storefront;

class StructuredDataBuilder
{
    public static function localBusiness(?string $description = null): array
    {
        $site = config('site');
        $local = $site['local'];
        $contact = config('conversion.contact');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => $site['url'].'/#organization',
            'name' => $local['company_name'],
            'description' => $description ?? $site['seo']['description'],
            'url' => $site['url'],
            'logo' => url($local['logo']),
            'image' => url($site['seo']['og_image']),
            'telephone' => $local['phone'] ?: $contact['phone'],
            'email' => $local['email'] ?: $contact['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $local['address'] ?: $contact['address'],
                'addressLocality' => $local['city'],
                'addressCountry' => $local['country_code'],
            ],
            'areaServed' => $local['service_areas'],
            'serviceArea' => [
                '@type' => 'Country',
                'name' => $local['country'],
            ],
            'openingHours' => array_values(array_filter([
                $local['opening_hours']['weekdays'] ?? null,
                $local['opening_hours']['saturday'] ?? null,
            ])),
        ];
    }

    public static function website(): array
    {
        $site = config('site');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $site['name'],
            'url' => $site['url'],
            'publisher' => [
                '@id' => $site['url'].'/#organization',
            ],
        ];
    }

    public static function service(string $name, string $description): array
    {
        $site = config('site');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $name,
            'description' => $description,
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => $site['name'],
                'url' => $site['url'],
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => config('site.local.country', 'Kenya'),
            ],
        ];
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $items
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(
                fn (array $item, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['label'],
                    'item' => $item['url'],
                ]
            )->all(),
        ];
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faqs
     */
    public static function faqPage(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ])->all(),
        ];
    }
}
