<?php

namespace App\Services\Storefront;

use App\Enums\WebsiteGalleryCategory;
use App\Models\WebsiteGalleryItem;
use App\Support\Storefront\PublicGalleryItemPresenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteGalleryService
{
    public function tableExists(): bool
    {
        return Schema::hasTable('website_gallery_items');
    }

    /**
     * @return Collection<int, WebsiteGalleryItem>
     */
    public function publishedItems(): Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        return WebsiteGalleryItem::query()
            ->where('is_published', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return Collection<int, WebsiteGalleryItem>
     */
    public function featuredPublished(int $limit = 3): Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        return WebsiteGalleryItem::query()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function homepageProjects(int $limit = 12): array
    {
        return app(PublicGalleryService::class)->homepageItems($limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function allPublicProjects(): array
    {
        return app(PublicGalleryService::class)->allItems();
    }

    /**
     * @return array<int, array{slug: string, label: string}>
     */
    public function categoryFilters(): array
    {
        return app(PublicGalleryService::class)->categoriesWithItems();
    }

    /**
     * @param  array<int, array<string, mixed>>  $projects
     * @return array<int, array{slug: string, label: string}>
     */
    public function categoryFiltersForProjects(array $projects): array
    {
        $filters = [['slug' => 'all', 'label' => 'All']];

        $labels = WebsiteGalleryCategory::options();

        foreach (collect($projects)->pluck('category')->unique()->sort()->values() as $slug) {
            $filters[] = [
                'slug' => $slug,
                'label' => $labels[$slug] ?? Str::headline(str_replace('-', ' ', $slug)),
            ];
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    public function toProjectArray(WebsiteGalleryItem $item): array
    {
        return PublicGalleryItemPresenter::fromModel($item);
    }

    public function storeImage(UploadedFile $file): string
    {
        return $file->store('website-gallery', 'public');
    }

    public function deleteStoredImage(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http') || str_starts_with($path, '/images/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function uniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title) ?: 'gallery-item';
        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $exceptId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $exceptId = null): bool
    {
        if (! $this->tableExists()) {
            return false;
        }

        return WebsiteGalleryItem::query()
            ->where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }
}
