<?php

namespace App\Support\Storefront;

class SitemapBuilder
{
    /**
     * @return array<int, array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>
     */
    public static function urls(): array
    {
        $urls = [
            self::entry(route('home'), 'weekly', '1.0'),
            self::entry(route('storefront.about'), 'monthly', '0.8'),
            self::entry(route('storefront.services'), 'weekly', '0.9'),
            self::entry(route('storefront.products'), 'weekly', '0.9'),
            self::entry(route('storefront.portfolio'), 'weekly', '0.8'),
            self::entry(route('storefront.blog'), 'monthly', '0.6'),
            self::entry(route('storefront.contact'), 'monthly', '0.8'),
            self::entry(route('storefront.quote'), 'monthly', '0.9'),
        ];

        foreach (config('capabilities.capabilities', []) as $capability) {
            $urls[] = self::entry(
                route('storefront.services.show', $capability['slug']),
                'monthly',
                '0.8'
            );
        }

        foreach (config('products.items', []) as $product) {
            $urls[] = self::entry(
                route('storefront.products.show', $product['slug']),
                'monthly',
                '0.7'
            );
        }

        return $urls;
    }

    public static function toXml(): string
    {
        $urls = self::urls();

        $items = collect($urls)->map(function (array $url) {
            $lines = [
                '  <url>',
                '    <loc>'.e($url['loc']).'</loc>',
            ];

            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>'.$url['lastmod'].'</lastmod>';
            }

            if (! empty($url['changefreq'])) {
                $lines[] = '    <changefreq>'.$url['changefreq'].'</changefreq>';
            }

            if (! empty($url['priority'])) {
                $lines[] = '    <priority>'.$url['priority'].'</priority>';
            }

            $lines[] = '  </url>';

            return implode("\n", $lines);
        })->implode("\n");

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{$items}
</urlset>
XML;
    }

    private static function entry(string $loc, string $changefreq, string $priority): array
    {
        return [
            'loc' => $loc,
            'lastmod' => now()->toDateString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
