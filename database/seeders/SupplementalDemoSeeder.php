<?php

namespace Database\Seeders;

use App\Support\Demo\SupplementalDemoSeedService;
use Illuminate\Database\Seeder;

class SupplementalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $sections = array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) env('DEMO_SEED_SECTIONS', '')),
        )));

        if ($sections !== []) {
            $this->command?->info('Supplemental demo sections: '.implode(', ', $sections));
        } else {
            $this->command?->info('Supplemental demo: all sections (accounting, sales, hr, supply_chain, assets, commercial)');
        }

        app(SupplementalDemoSeedService::class)->run($this->command, $sections);

        $this->command?->newLine();
        $this->command?->info('Supplemental demo seed complete.');
        $this->command?->line('  Tip: DEMO_SEED_SECTIONS=accounting,hr php artisan db:seed --class=SupplementalDemoSeeder');
    }
}
