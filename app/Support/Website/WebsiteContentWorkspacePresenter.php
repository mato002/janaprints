<?php

namespace App\Support\Website;

use App\Models\WebsiteGalleryItem;
use App\Models\WebsiteMediaItem;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class WebsiteContentWorkspacePresenter
{
    /**
     * @return array<string, array{statusLabel: string, statusVariant: string}>
     */
    public function cardStats(): array
    {
        return [
            'gallery' => $this->galleryStats(),
            'media-library' => $this->mediaStats(),
            'footer-contact' => $this->footerContactStats(),
            'seo-global' => $this->seoGlobalStats(),
        ];
    }

    /**
     * @return array{statusLabel: string, statusVariant: string}
     */
    protected function galleryStats(): array
    {
        if (! Schema::hasTable('website_gallery_items')) {
            return ['statusLabel' => __('Gallery'), 'statusVariant' => 'neutral'];
        }

        $total = WebsiteGalleryItem::query()->count();
        $published = WebsiteGalleryItem::query()->where('is_published', true)->count();
        $featured = WebsiteGalleryItem::query()->where('is_featured', true)->count();

        return [
            'statusLabel' => __(':total items · :published published · :featured featured', [
                'total' => $total,
                'published' => $published,
                'featured' => $featured,
            ]),
            'statusVariant' => $published > 0 ? 'success' : 'neutral',
        ];
    }

    /**
     * @return array{statusLabel: string, statusVariant: string}
     */
    protected function mediaStats(): array
    {
        if (! Schema::hasTable('website_media_items')) {
            return ['statusLabel' => __('Media Library'), 'statusVariant' => 'neutral'];
        }

        $total = WebsiteMediaItem::query()->count();
        $uploaded = WebsiteMediaItem::query()->whereNotNull('image_path')->where('image_path', '!=', '')->count();
        $inactive = WebsiteMediaItem::query()->where('is_active', false)->count();

        return [
            'statusLabel' => __(':total slots · :uploaded uploaded · :inactive inactive', [
                'total' => $total,
                'uploaded' => $uploaded,
                'inactive' => $inactive,
            ]),
            'statusVariant' => $uploaded > 0 ? 'success' : 'neutral',
        ];
    }

    /**
     * @return array{statusLabel: string, statusVariant: string}
     */
    protected function footerContactStats(): array
    {
        if (! Schema::hasTable('website_settings')) {
            return ['statusLabel' => __('Footer & Contact'), 'statusVariant' => 'neutral'];
        }

        $keys = collect(config('website_cms.settings', []))
            ->filter(fn (array $meta) => in_array('footer-contact', $meta['admin_pages'] ?? [], true))
            ->keys();

        $customized = WebsiteSetting::query()
            ->whereIn('key', $keys)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();

        return [
            'statusLabel' => __(':count customized settings', ['count' => $customized]),
            'statusVariant' => $customized > 0 ? 'success' : 'neutral',
        ];
    }

    /**
     * @return array{statusLabel: string, statusVariant: string}
     */
    protected function seoGlobalStats(): array
    {
        if (! Schema::hasTable('website_settings')) {
            return ['statusLabel' => __('SEO & Global'), 'statusVariant' => 'neutral'];
        }

        $seoKeys = ['site.name', 'site.tagline', 'seo.title', 'seo.description', 'seo.keywords', 'seo.og_image'];
        $customized = WebsiteSetting::query()
            ->whereIn('key', $seoKeys)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();

        return [
            'statusLabel' => __(':count SEO fields customized', ['count' => $customized]),
            'statusVariant' => $customized > 0 ? 'success' : 'neutral',
        ];
    }

    /**
     * @return list<array{key: string, label: string, ready: bool, detail: string}>
     */
    public function deploymentChecklist(): array
    {
        $storageLinked = File::exists(public_path('storage'))
            && (is_link(public_path('storage')) || File::isDirectory(public_path('storage')));

        $mediaSeeded = Schema::hasTable('website_media_items')
            && WebsiteMediaItem::query()->count() >= 30;

        $settingsSeeded = Schema::hasTable('website_settings')
            && WebsiteSetting::query()->count() >= 20;

        $galleryActive = ! Schema::hasTable('website_gallery_items')
            || WebsiteGalleryItem::query()->where('is_published', true)->exists();

        $footerConfigured = ! Schema::hasTable('website_settings')
            || WebsiteSetting::query()
                ->whereIn('key', ['footer.tagline', 'contact.phone', 'contact.email'])
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->exists();

        return [
            [
                'key' => 'storage_link',
                'label' => __('Storage Link'),
                'ready' => $storageLinked,
                'detail' => $storageLinked
                    ? __('Public storage symlink is available.')
                    : __('Run php artisan storage:link on the server.'),
            ],
            [
                'key' => 'media_seed',
                'label' => __('Media Seed Complete'),
                'ready' => $mediaSeeded,
                'detail' => $mediaSeeded
                    ? __('Media slots are registered in the database.')
                    : __('Run php artisan website:content-baseline to seed media slots.'),
            ],
            [
                'key' => 'settings_seed',
                'label' => __('Settings Seed Complete'),
                'ready' => $settingsSeeded,
                'detail' => $settingsSeeded
                    ? __('Website settings registry rows exist.')
                    : __('Run php artisan website:content-baseline to seed settings.'),
            ],
            [
                'key' => 'gallery_active',
                'label' => __('Gallery Active'),
                'ready' => $galleryActive,
                'detail' => $galleryActive
                    ? __('At least one gallery item is published, or gallery is ready to publish.')
                    : __('Publish a gallery project to show live portfolio items.'),
            ],
            [
                'key' => 'footer_configured',
                'label' => __('Footer Configured'),
                'ready' => $footerConfigured,
                'detail' => $footerConfigured
                    ? __('Footer or contact details have been saved in CMS.')
                    : __('Save footer description, phone, or email in Footer & Contact Settings.'),
            ],
        ];
    }
}
