<?php

namespace App\Support\Storefront;

class SeoMeta
{
    /**
     * @param  array<int, array<string, mixed>>  $structuredData
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $canonical,
        public readonly string $robots = 'index, follow',
        public readonly ?string $ogImage = null,
        public readonly string $ogType = 'website',
        public readonly ?string $twitterTitle = null,
        public readonly ?string $twitterDescription = null,
        public readonly ?string $twitterImage = null,
        public readonly array $structuredData = [],
    ) {}

    public function ogImageUrl(): string
    {
        $image = $this->ogImage ?? app(\App\Services\Website\WebsiteMediaResolver::class)->resolvePath('seo.og_image');

        return str_starts_with((string) $image, 'http')
            ? (string) $image
            : url((string) $image);
    }

    public function twitterTitle(): string
    {
        return $this->twitterTitle ?? $this->title;
    }

    public function twitterDescription(): string
    {
        return $this->twitterDescription ?? $this->description;
    }

    public function twitterImageUrl(): string
    {
        $image = $this->twitterImage ?? $this->ogImage ?? app(\App\Services\Website\WebsiteMediaResolver::class)->resolvePath('seo.og_image');

        return str_starts_with((string) $image, 'http')
            ? (string) $image
            : url((string) $image);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public static function forPage(string $pageKey, array $overrides = [], array $structuredData = []): self
    {
        $site = config('site');
        $page = array_merge(config("storefront.pages.{$pageKey}", []), $overrides);

        return new self(
            title: (string) ($page['title'] ?? $site['seo']['title']),
            description: (string) ($page['description'] ?? $site['seo']['description']),
            canonical: (string) ($page['canonical'] ?? route($pageKey === 'home' ? 'home' : "storefront.{$pageKey}")),
            robots: (string) ($page['robots'] ?? 'index, follow'),
            ogImage: $page['og_image'] ?? null,
            ogType: (string) ($page['og_type'] ?? 'website'),
            twitterTitle: $page['twitter_title'] ?? null,
            twitterDescription: $page['twitter_description'] ?? null,
            twitterImage: $page['twitter_image'] ?? null,
            structuredData: $structuredData,
        );
    }

    /**
     * @param  array<string, mixed>  $serviceSeo
     * @param  array<int, array<string, mixed>>  $structuredData
     */
    public static function forService(array $serviceSeo, string $canonical, array $structuredData = []): self
    {
        $site = config('site');

        return new self(
            title: (string) ($serviceSeo['title'] ?? $site['seo']['title']),
            description: (string) ($serviceSeo['description'] ?? $site['seo']['description']),
            canonical: $canonical,
            ogImage: $serviceSeo['og_image'] ?? null,
            ogType: 'website',
            structuredData: $structuredData,
        );
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<int, array<string, mixed>>  $structuredData
     */
    public static function forProduct(array $product, string $canonical, array $structuredData = []): self
    {
        $seo = $product['seo'] ?? [];
        $site = config('site');

        return new self(
            title: (string) ($seo['title'] ?? $product['name'].' | '.$site['name']),
            description: (string) ($seo['description'] ?? $product['summary']),
            canonical: $canonical,
            ogType: 'product',
            structuredData: $structuredData,
        );
    }
}
