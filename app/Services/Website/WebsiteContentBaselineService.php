<?php

namespace App\Services\Website;

use App\Models\WebsiteMediaItem;
use App\Models\WebsiteSetting;
use App\Support\Website\WebsiteContentBaselineBuilder;
use Illuminate\Support\Facades\Schema;

class WebsiteContentBaselineService
{
    public function __construct(
        protected WebsiteContentBaselineBuilder $builder,
    ) {}

    /**
     * @return array{media_created: int, media_updated: int, settings_created: int, settings_updated: int}
     */
    public function seed(): array
    {
        if (! Schema::hasTable('website_media_items') || ! Schema::hasTable('website_settings')) {
            return [
                'media_created' => 0,
                'media_updated' => 0,
                'settings_created' => 0,
                'settings_updated' => 0,
            ];
        }

        $mediaStats = $this->seedMediaSlots();
        $settingsStats = $this->seedSettings();

        return array_merge($mediaStats, $settingsStats);
    }

    /**
     * @return array{media_created: int, media_updated: int}
     */
    public function seedMediaSlots(): array
    {
        $created = 0;
        $updated = 0;

        foreach ($this->builder->mediaSlots() as $slot) {
            $existing = WebsiteMediaItem::query()
                ->where('slot_key', $slot['slot_key'])
                ->first();

            if (! $existing) {
                WebsiteMediaItem::query()->create([
                    'slot_key' => $slot['slot_key'],
                    'section' => $slot['section'],
                    'label' => $slot['label'],
                    'fallback_path' => $slot['fallback_path'],
                    'alt_text' => $slot['alt_text'],
                    'sort_order' => $slot['sort_order'],
                    'is_active' => $slot['is_active'],
                    'image_path' => null,
                ]);
                $created++;

                continue;
            }

            $changes = [];

            if (empty($existing->fallback_path) && ! empty($slot['fallback_path'])) {
                $changes['fallback_path'] = $slot['fallback_path'];
            }

            if ($changes !== []) {
                $existing->update($changes);
                $updated++;
            }
        }

        return [
            'media_created' => $created,
            'media_updated' => $updated,
        ];
    }

    /**
     * @return array{settings_created: int, settings_updated: int}
     */
    public function seedSettings(): array
    {
        $created = 0;
        $updated = 0;

        foreach ($this->builder->settings() as $key => $setting) {
            $existing = WebsiteSetting::query()->where('key', $key)->first();

            if (! $existing) {
                WebsiteSetting::query()->create([
                    'key' => $key,
                    'group' => $setting['group'],
                    'type' => $setting['type'],
                    'fallback_value' => $setting['fallback_value'],
                    'value' => null,
                    'is_active' => true,
                ]);
                $created++;

                continue;
            }

            $changes = [];

            if (! $this->hasStoredValue($existing->fallback_value) && $this->hasStoredValue($setting['fallback_value'])) {
                $changes['fallback_value'] = $setting['fallback_value'];
            }

            if ($changes !== []) {
                $existing->update($changes);
                $updated++;
            }
        }

        return [
            'settings_created' => $created,
            'settings_updated' => $updated,
        ];
    }

    public function mediaSlotCount(): int
    {
        return count($this->builder->mediaSlots());
    }

    public function settingsCount(): int
    {
        return count($this->builder->settings());
    }

    protected function hasStoredValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }
}
