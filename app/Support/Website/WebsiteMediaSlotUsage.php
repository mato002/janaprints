<?php

namespace App\Support\Website;

class WebsiteMediaSlotUsage
{
    /**
     * @return array<string, string>
     */
    public function sectionDefaults(): array
    {
        return [
            'hero' => __('Homepage hero collage and mobile strip'),
            'services' => __('Homepage services grid and service detail pages'),
            'products' => __('Product catalogue grid and product detail pages'),
            'inside_jana' => __('Inside Jana Prints section on homepage'),
            'workflow' => __('Production workflow section on homepage'),
            'team' => __('Team showcase section on homepage'),
            'quality' => __('Quality promise section on homepage'),
            'testimonials' => __('Testimonials and customer stories on homepage'),
            'gallery_preview' => __('Recent work preview fallback thumbnails'),
            'cta' => __('Conversion and CTA sections'),
            'branding' => __('Branding reference imagery'),
            'facility' => __('Facility and production imagery'),
        ];
    }

    public function labelFor(string $slotKey, string $section): string
    {
        $overrides = config('website_cms.media_slot_usage', []);

        if (isset($overrides[$slotKey])) {
            return (string) $overrides[$slotKey];
        }

        if (str_starts_with($slotKey, 'services.')) {
            return __('Services section — :name', ['name' => str_replace('-', ' ', substr($slotKey, 9))]);
        }

        if (str_starts_with($slotKey, 'products.')) {
            return __('Products catalogue — :name', ['name' => str_replace('-', ' ', substr($slotKey, 9))]);
        }

        if (str_starts_with($slotKey, 'testimonial.')) {
            return __('Testimonials section');
        }

        if (in_array($slotKey, ['seo.og_image', 'gallery.og_image'], true)) {
            return __('SEO / social sharing image');
        }

        return $this->sectionDefaults()[$section] ?? __('Public storefront');
    }
}
