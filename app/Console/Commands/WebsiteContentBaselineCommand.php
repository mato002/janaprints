<?php

namespace App\Console\Commands;

use App\Services\Website\WebsiteContentBaselineService;
use Illuminate\Console\Command;

class WebsiteContentBaselineCommand extends Command
{
    protected $signature = 'website:content-baseline';

    protected $description = 'Seed public website media slots and footer/contact settings from current static config (idempotent)';

    public function handle(WebsiteContentBaselineService $baseline): int
    {
        $expectedMedia = $baseline->mediaSlotCount();
        $expectedSettings = $baseline->settingsCount();

        $stats = $baseline->seed();

        $this->info(__('Website content baseline seed completed.'));
        $this->line(__('Expected media slots: :count', ['count' => $expectedMedia]));
        $this->line(__('Expected settings keys: :count', ['count' => $expectedSettings]));
        $this->line(__('Media created: :count', ['count' => $stats['media_created']]));
        $this->line(__('Media fallbacks updated: :count', ['count' => $stats['media_updated']]));
        $this->line(__('Settings created: :count', ['count' => $stats['settings_created']]));
        $this->line(__('Settings fallbacks updated: :count', ['count' => $stats['settings_updated']]));
        $this->line(__('Total media records: :count', ['count' => \App\Models\WebsiteMediaItem::query()->count()]));
        $this->line(__('Total settings records: :count', ['count' => \App\Models\WebsiteSetting::query()->count()]));

        return self::SUCCESS;
    }
}
