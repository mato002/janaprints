<?php

namespace App\Support\Website;

use Illuminate\Support\Str;

class WebsiteContentBaselineBuilder
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function mediaSlots(): array
    {
        $publicImages = config('public-images', []);
        $slots = array_merge(
            config('website_cms.media_slots', []),
            config('website_content.media_slots', []),
        );

        foreach (config('capabilities.capabilities', []) as $index => $capability) {
            $slug = $capability['slug'] ?? ('service-'.$index);
            $key = 'services.'.$slug;
            if (! isset($slots[$key])) {
                $slots[$key] = [
                    'section' => 'services',
                    'label' => 'Service — '.($capability['title'] ?? $slug),
                    'image_key' => $this->imageKeyFromPath($capability['image'] ?? '', $publicImages),
                    'fallback_path' => is_string($capability['image'] ?? null) ? $capability['image'] : null,
                    'alt' => $capability['alt'] ?? ($capability['title'] ?? $slug),
                    'sort_order' => 100 + ($index * 10),
                ];
            }
        }

        foreach (config('products.items', []) as $index => $product) {
            $slug = $product['slug'] ?? ('product-'.$index);
            $key = 'products.'.$slug;
            if (! isset($slots[$key])) {
                $slots[$key] = [
                    'section' => 'services',
                    'label' => 'Product — '.($product['name'] ?? $slug),
                    'image_key' => $this->imageKeyFromPath($product['image'] ?? '', $publicImages),
                    'fallback_path' => is_string($product['image'] ?? null) ? $product['image'] : null,
                    'alt' => $product['alt'] ?? ($product['name'] ?? $slug),
                    'sort_order' => 200 + ($index * 10),
                ];
            }
        }

        foreach (config('facility.gallery', []) as $index => $item) {
            $path = $item['image'] ?? '';
            $basename = pathinfo((string) $path, PATHINFO_FILENAME) ?: 'item-'.$index;
            $key = 'gallery_preview.'.$basename;
            if (! isset($slots[$key])) {
                $slots[$key] = [
                    'section' => 'gallery_preview',
                    'label' => 'Gallery Preview — '.Str::headline(str_replace('-', ' ', $basename)),
                    'fallback_path' => $path,
                    'alt' => $item['alt'] ?? Str::headline($basename),
                    'sort_order' => 10 + ($index * 10),
                ];
            }
        }

        return collect($slots)
            ->map(function (array $meta, string $slotKey) use ($publicImages) {
                $fallbackPath = $meta['fallback_path']
                    ?? ($meta['image_key'] ?? null ? ($publicImages[$meta['image_key']] ?? null) : null);

                return [
                    'slot_key' => $slotKey,
                    'section' => $meta['section'],
                    'label' => $meta['label'] ?? $slotKey,
                    'fallback_path' => $fallbackPath,
                    'alt_text' => $meta['alt'] ?? $meta['label'] ?? $slotKey,
                    'sort_order' => (int) ($meta['sort_order'] ?? 0),
                    'is_active' => true,
                ];
            })
            ->all();
    }

    /**
     * @return array<string, array{group: string, type: string, fallback_value: ?string}>
     */
    public function settings(): array
    {
        $schema = config('website_cms.settings', []);
        $baseline = config('website_content.settings', []);
        $resolved = [];

        foreach ($baseline as $key => $_) {
            if (! isset($schema[$key])) {
                continue;
            }

            $meta = $schema[$key];
            $value = $this->resolveSettingValue($key, $meta['type']);

            $resolved[$key] = [
                'group' => $meta['group'],
                'type' => $meta['type'],
                'fallback_value' => $this->serializeSettingValue($value, $meta['type']),
            ];
        }

        return $resolved;
    }

    protected function resolveSettingValue(string $key, string $type): mixed
    {
        return match ($key) {
            'site.name' => config('site.name'),
            'site.tagline' => config('site.tagline'),
            'footer.tagline' => config('site.footer.tagline'),
            'footer.nav' => config('site.footer.nav'),
            'footer.trust_badges' => config('site.footer.trust_badges'),
            'footer.social' => config('site.footer.social'),
            'footer.copyright' => config('site.name').' — All rights reserved.',
            'footer.location_suffix' => 'Nairobi, Kenya',
            'contact.phone' => config('conversion.contact.phone'),
            'contact.phone_href' => config('conversion.contact.phone_href'),
            'contact.email' => config('conversion.contact.email'),
            'contact.email_href' => config('conversion.contact.email_href'),
            'contact.address' => config('conversion.contact.address'),
            'contact.address_detail' => config('conversion.contact.address_detail'),
            'contact.hours' => config('conversion.contact.hours'),
            'contact.hours_weekend' => config('conversion.contact.hours_weekend'),
            'contact.map_latitude' => (string) config('conversion.contact.map_latitude'),
            'contact.map_longitude' => (string) config('conversion.contact.map_longitude'),
            'contact.map_zoom' => (string) config('conversion.contact.map_zoom'),
            'contact.map_embed' => config('conversion.contact.map_embed'),
            'contact.map_url' => config('conversion.contact.map_url'),
            'contact.map_placeholder' => config('conversion.contact.map_placeholder'),
            'contact.map_enabled' => config('conversion.contact.map_enabled', true) ? '1' : '0',
            'whatsapp.number' => config('conversion.whatsapp.number'),
            'whatsapp.message' => config('conversion.whatsapp.message'),
            'whatsapp.response_time' => config('conversion.whatsapp.response_time'),
            'social.facebook' => $this->socialHref('facebook'),
            'social.instagram' => $this->socialHref('instagram'),
            'social.linkedin' => $this->socialHref('linkedin'),
            'social.twitter' => $this->socialHref('twitter'),
            'seo.title' => config('site.seo.title'),
            'seo.description' => config('site.seo.description'),
            'seo.keywords' => config('site.seo.keywords'),
            'seo.og_image' => config('site.seo.og_image'),
            default => null,
        };
    }

    protected function socialHref(string $icon): ?string
    {
        foreach (config('site.footer.social', []) as $social) {
            if (($social['icon'] ?? '') === $icon) {
                return $social['href'] ?? null;
            }
        }

        return null;
    }

    protected function serializeSettingValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($type === 'json') {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * @param  array<string, string>  $publicImages
     */
    protected function imageKeyFromPath(string $path, array $publicImages): ?string
    {
        foreach ($publicImages as $key => $imagePath) {
            if ($imagePath === $path) {
                return $key;
            }
        }

        return null;
    }
}
