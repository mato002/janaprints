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
