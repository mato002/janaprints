<?php

namespace Database\Seeders;

use App\Services\Website\WebsiteContentBaselineService;
use Illuminate\Database\Seeder;

class WebsiteContentBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $stats = app(WebsiteContentBaselineService::class)->seed();

        if ($this->command) {
            $this->command->info(sprintf(
                'Website baseline: %d media created, %d media fallbacks updated, %d settings created, %d settings fallbacks updated.',
                $stats['media_created'],
                $stats['media_updated'],
                $stats['settings_created'],
                $stats['settings_updated'],
            ));
        }
    }
}
