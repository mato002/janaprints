<?php

namespace App\Services\Website;

use App\Models\WebsiteMediaItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WebsiteMediaService
{
    public function tableExists(): bool
    {
        return Schema::hasTable('website_media_items');
    }

    public function storeImage(UploadedFile $file): string
    {
        return $file->store('website-media', 'public');
    }

    public function deleteStoredImage(?string $path): void
    {
        if (! $path || ! $this->isManagedStoragePath($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function isManagedStoragePath(string $path): bool
    {
        return str_starts_with($path, 'website-media/');
    }

    /**
     * @return Collection<int, WebsiteMediaItem>
     */
    public function syncRegistrySlots(): Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        app(WebsiteContentBaselineService::class)->seedMediaSlots();

        return WebsiteMediaItem::query()
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('slot_key')
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function sectionLabels(): array
    {
        $labels = array_merge(
            config('website_cms.media_sections', []),
            config('website_content.media_sections', []),
        );

        $labels = collect($labels)
            ->mapWithKeys(fn (string $label, string $key) => [$key => $this->displaySectionLabel($key, $label)])
            ->all();

        $labels['products'] = __('Products');

        return $labels;
    }

    public function displaySectionLabel(string $key, string $label): string
    {
        return match ($key) {
            'services' => __('Services & Capabilities'),
            'products' => __('Products'),
            'cta' => __('CTA / SEO'),
            default => $label,
        };
    }

    /**
     * @param  Collection<int, WebsiteMediaItem>  $items
     * @return Collection<int, WebsiteMediaItem>
     */
    public function filterBySection(Collection $items, string $section): Collection
    {
        if ($section === 'products') {
            return $items
                ->filter(fn (WebsiteMediaItem $item) => str_starts_with((string) $item->slot_key, 'products.'))
                ->values();
        }

        if ($section === 'services') {
            return $items
                ->filter(fn (WebsiteMediaItem $item) => $item->section === 'services'
                    && ! str_starts_with((string) $item->slot_key, 'products.'))
                ->values();
        }

        return $items->where('section', $section)->values();
    }

    /**
     * @param  Collection<int, WebsiteMediaItem>  $items
     * @param  array<string, mixed>  $filters
     * @return Collection<int, WebsiteMediaItem>
     */
    public function filterItems(Collection $items, array $filters): Collection
    {
        $query = trim((string) ($filters['q'] ?? ''));

        if ($query !== '') {
            $needle = strtolower($query);
            $items = $items->filter(function (WebsiteMediaItem $item) use ($needle) {
                return str_contains(strtolower((string) $item->label), $needle)
                    || str_contains(strtolower((string) $item->slot_key), $needle);
            });
        }

        if (($filters['status'] ?? '') === 'active') {
            $items = $items->where('is_active', true);
        } elseif (($filters['status'] ?? '') === 'inactive') {
            $items = $items->where('is_active', false);
        }

        if (($filters['source'] ?? '') === 'uploaded') {
            $items = $items->filter(fn (WebsiteMediaItem $item) => $item->hasUploadedImage());
        } elseif (($filters['source'] ?? '') === 'fallback') {
            $items = $items->filter(fn (WebsiteMediaItem $item) => ! $item->hasUploadedImage());
        }

        return $items->values();
    }

    /**
     * @return array{total: int, uploaded: int, inactive: int}
     */
    public function summaryCounts(Collection $items): array
    {
        return [
            'total' => $items->count(),
            'uploaded' => $items->filter(fn (WebsiteMediaItem $item) => $item->hasUploadedImage())->count(),
            'inactive' => $items->where('is_active', false)->count(),
        ];
    }
}
