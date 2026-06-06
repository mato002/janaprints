<?php

namespace App\Support\Storefront;

use App\Models\WebsiteGalleryItem;
use Illuminate\Support\Str;

class PublicGalleryItemPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function fromModel(WebsiteGalleryItem $item): array
    {
        return self::present(
            id: $item->slug,
            title: $item->title,
            category: $item->category,
            categoryLabel: $item->categoryLabel(),
            image: $item->publicImageUrl(),
            alt: $item->alt_text ?: $item->title,
            description: $item->description,
            slug: $item->slug,
            location: $item->location,
            quantityLabel: $item->quantity_label,
            timelineLabel: $item->timeline_label,
            materialsLabel: $item->materials_label,
            outcome: $item->outcome,
        );
    }

    /**
     * @param  array<string, mixed>  $project
     * @return array<string, mixed>
     */
    public static function fromFallback(array $project): array
    {
        return self::present(
            id: (string) ($project['id'] ?? $project['slug'] ?? ''),
            title: (string) $project['title'],
            category: (string) $project['category'],
            categoryLabel: (string) ($project['category_label'] ?? Str::headline(str_replace('-', ' ', $project['category']))),
            image: (string) $project['image'],
            alt: (string) ($project['alt'] ?? $project['title']),
            description: (string) ($project['description'] ?? ''),
            slug: (string) ($project['id'] ?? $project['slug'] ?? ''),
            caption: (string) ($project['caption'] ?? ''),
            location: (string) ($project['location'] ?? ''),
            quantityLabel: (string) ($project['quantity_label'] ?? $project['quantity'] ?? ''),
            timelineLabel: (string) ($project['timeline_label'] ?? $project['timeline'] ?? ''),
            materialsLabel: (string) ($project['materials_label'] ?? $project['materials'] ?? ''),
            outcome: (string) ($project['outcome'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected static function present(
        string $id,
        string $title,
        string $category,
        string $categoryLabel,
        string $image,
        string $alt,
        ?string $description,
        string $slug,
        string $caption = '',
        ?string $location = null,
        ?string $quantityLabel = null,
        ?string $timelineLabel = null,
        ?string $materialsLabel = null,
        ?string $outcome = null,
    ): array {
        $publicDescription = trim((string) $description);

        return [
            'id' => $id,
            'slug' => $slug,
            'title' => $title,
            'category' => $category,
            'category_label' => $categoryLabel,
            'caption' => $caption !== '' ? $caption : self::captionFromDescription($publicDescription),
            'image' => $image,
            'image_url' => $image,
            'alt' => $alt,
            'alt_text' => $alt,
            'description' => $publicDescription,
            'location' => trim((string) $location),
            'quantity_label' => trim((string) $quantityLabel),
            'timeline_label' => trim((string) $timelineLabel),
            'materials_label' => trim((string) $materialsLabel),
            'outcome' => trim((string) $outcome),
            'is_featured' => false,
        ];
    }

    protected static function captionFromDescription(string $description): string
    {
        if ($description === '') {
            return '';
        }

        return Str::length($description) > 90
            ? Str::substr($description, 0, 87).'...'
            : $description;
    }
}
