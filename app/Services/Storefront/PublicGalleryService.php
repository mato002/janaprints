<?php

namespace App\Services\Storefront;

use App\Models\WebsiteGalleryItem;
use App\Support\Storefront\PublicGalleryItemPresenter;
use Illuminate\Support\Str;

class PublicGalleryService
{
    /**
     * @var array<string, array{title: string, category: string}>
     */
    private const FACILITY_GALLERY_META = [
        'print-production' => ['title' => 'Commercial Print Production', 'category' => 'large-format'],
        'packaging' => ['title' => 'Product Packaging', 'category' => 'packaging'],
        'large-format' => ['title' => 'Large Format Banners', 'category' => 'large-format'],
        'business-cards' => ['title' => 'Premium Business Cards', 'category' => 'business-cards'],
        'vehicle-branding' => ['title' => 'Vehicle Branding', 'category' => 'vehicle-branding'],
        'stationery' => ['title' => 'Corporate Stationery', 'category' => 'corporate-stationery'],
        'design-studio' => ['title' => 'Design Studio Work', 'category' => 'branding-installations'],
        'brochures' => ['title' => 'Brochure Printing', 'category' => 'brochures'],
        'branding' => ['title' => 'Brand Collateral', 'category' => 'branding-installations'],
        'events' => ['title' => 'Event Print Materials', 'category' => 'events-exhibitions'],
        'flyers' => ['title' => 'Flyer Printing', 'category' => 'flyers'],
        'labels' => ['title' => 'Product Labels', 'category' => 'labels-stickers'],
    ];

    public function __construct(
        protected WebsiteGalleryService $websiteGallery,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allItems(): array
    {
        return $this->mergedItems();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function featuredItems(int $limit = 12): array
    {
        return collect($this->mergedItems())
            ->filter(fn (array $item) => ! empty($item['is_featured']))
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function homepageItems(int $limit = 12): array
    {
        return collect($this->mergedItems())
            ->sortByDesc(fn (array $item) => $item['is_featured'] ? 1 : 0)
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function itemsByCategory(string $category): array
    {
        if ($category === 'all') {
            return $this->allItems();
        }

        return collect($this->mergedItems())
            ->where('category', $category)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{slug: string, label: string}>
     */
    public function categoriesWithItems(): array
    {
        return $this->websiteGallery->categoryFiltersForProjects($this->allItems());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function mergedItems(): array
    {
        $seen = [];
        $merged = [];

        foreach ($this->websiteGallery->publishedItems() as $item) {
            $presented = $this->presentAdminItem($item);
            $key = $this->dedupeKey($presented);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $merged[] = $presented;
        }

        foreach (config('portfolio.projects', []) as $project) {
            $presented = PublicGalleryItemPresenter::fromFallback($project);
            $key = $this->dedupeKey($presented);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $merged[] = $presented;
        }

        foreach ($this->curatedFacilityItems() as $presented) {
            $key = $this->dedupeKey($presented);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $merged[] = $presented;
        }

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentAdminItem(WebsiteGalleryItem $item): array
    {
        return array_merge(PublicGalleryItemPresenter::fromModel($item), [
            'is_featured' => (bool) $item->is_featured,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function curatedFacilityItems(): array
    {
        $items = [];

        foreach (config('facility.gallery', []) as $entry) {
            $image = (string) ($entry['image'] ?? '');
            $basename = pathinfo($image, PATHINFO_FILENAME);
            $meta = self::FACILITY_GALLERY_META[$basename] ?? [
                'title' => Str::headline(str_replace('-', ' ', $basename)),
                'category' => 'branding-installations',
            ];

            $items[] = PublicGalleryItemPresenter::fromFallback([
                'id' => 'facility-'.$basename,
                'slug' => 'facility-'.$basename,
                'title' => $meta['title'],
                'category' => $meta['category'],
                'image' => $image,
                'alt' => (string) ($entry['alt'] ?? $meta['title']),
                'description' => '',
            ]);
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function dedupeKey(array $item): string
    {
        $image = strtolower(trim((string) ($item['image'] ?? '')));
        $slug = strtolower(trim((string) ($item['slug'] ?? $item['id'] ?? '')));

        return $image !== '' ? $image : $slug;
    }
}
